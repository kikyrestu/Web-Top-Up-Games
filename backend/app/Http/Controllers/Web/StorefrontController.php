<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Security\Services\TamperRiskService;
use App\Domain\Order\Services\OrderService;
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Pricing\Services\PricingEngineService;
use App\Http\Controllers\Controller;
use App\Models\Margin;
use App\Models\Order;
use App\Models\PaymentGatewaySetting;
use App\Models\Product;
use App\Models\ProviderPrice;
use App\Models\ProviderProduct;
use App\Models\SecurityEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class StorefrontController extends Controller
{
    public function __construct(
        private readonly PricingEngineService $pricingEngine,
        private readonly OrderService $orderService,
        private readonly PaymentService $paymentService,
        private readonly TamperRiskService $tamperRiskService,
    ) {
    }

    public function index(): View
    {
        $productsByCategory = Product::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->orderBy('category_id')
            ->orderBy('name')
            ->get()
            ->groupBy(static fn (Product $product): string => (string) ($product->category?->name ?? 'Lainnya'));

        $startingPrices = ProviderPrice::query()
            ->where('is_active', true)
            ->whereHas('provider', static fn ($query) => $query->where('is_active', true))
            ->selectRaw('product_id, MIN(base_price + admin_fee) as starting_price')
            ->groupBy('product_id')
            ->pluck('starting_price', 'product_id')
            ->map(static fn ($price): float => (float) $price)
            ->all();

        $defaultGateway = $this->resolveGateway();

        return view('storefront.index', [
            'productsByCategory' => $productsByCategory,
            'startingPrices' => $startingPrices,
            'defaultGateway' => $defaultGateway,
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
            'customer_target' => ['nullable', 'string', 'max:120'],
            'method' => ['nullable', 'string', 'max:50'],
            'security_challenge_answer' => ['nullable', 'string', 'max:20'],
        ]);

        $gateway = $this->resolveGateway($request->input('gateway'));
        $validated['gateway'] = $gateway;

        $risk = $this->tamperRiskService->evaluateCheckout($request, $validated);
        $riskScore = (int) ($risk['score'] ?? 0);
        $riskLevel = (string) ($risk['level'] ?? 'LOW');

        if ($riskLevel === 'HIGH') {
            $this->logSecurityEvent($request, 'CHECKOUT_TAMPER_BLOCKED', 'HIGH', $riskScore, [
                'reasons' => $risk['reasons'] ?? [],
                'product_id' => (int) ($validated['product_id'] ?? 0),
                'gateway' => (string) ($validated['gateway'] ?? ''),
            ]);

            return back()
                ->withErrors(['checkout' => 'Permintaan checkout terdeteksi berisiko tinggi dan diblok sementara.'])
                ->withInput();
        }

        if ($riskLevel === 'MEDIUM') {
            $challengeAnswer = trim((string) ($validated['security_challenge_answer'] ?? ''));
            if (!$this->tamperRiskService->verifyChallenge($request, $challengeAnswer)) {
                $challengeQuestion = $this->tamperRiskService->getOrCreateChallenge($request);

                $this->logSecurityEvent($request, 'CHECKOUT_TAMPER_CHALLENGE_REQUIRED', 'MEDIUM', $riskScore, [
                    'reasons' => $risk['reasons'] ?? [],
                    'product_id' => (int) ($validated['product_id'] ?? 0),
                    'gateway' => (string) ($validated['gateway'] ?? ''),
                ]);

                return back()
                    ->withErrors(['checkout' => 'Verifikasi keamanan diperlukan sebelum checkout dilanjutkan.'])
                    ->withInput()
                    ->with('checkout_challenge_question', $challengeQuestion);
            }

            $this->tamperRiskService->clearChallenge($request);

            $this->logSecurityEvent($request, 'CHECKOUT_TAMPER_CHALLENGE_PASSED', 'LOW', $riskScore, [
                'reasons' => $risk['reasons'] ?? [],
                'product_id' => (int) ($validated['product_id'] ?? 0),
                'gateway' => (string) ($validated['gateway'] ?? ''),
            ]);
        }

        $quantity = (int) ($validated['quantity'] ?? 1);

        $product = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->find($validated['product_id']);

        if ($product === null) {
            return back()
                ->withErrors(['product_id' => 'Produk tidak tersedia'])
                ->withInput();
        }

        $rows = ProviderPrice::query()
            ->with('provider')
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->get()
            ->filter(static fn (ProviderPrice $price): bool => (bool) ($price->provider?->is_active))
            ->values();

        if ($rows->isEmpty()) {
            return back()
                ->withErrors(['product_id' => 'Belum ada provider aktif untuk produk ini'])
                ->withInput();
        }

        $providerProductMap = ProviderProduct::query()
            ->where('product_id', $product->id)
            ->where('is_available', true)
            ->get()
            ->keyBy(static fn (ProviderProduct $providerProduct): string => $providerProduct->provider_id.'_'.$providerProduct->product_id);

        $candidates = $rows->map(function (ProviderPrice $row) use ($providerProductMap): array {
            $basePrice = (float) $row->base_price;
            $adminFee = (float) $row->admin_fee;
            $commission = (float) $row->commission;

            /** @var ProviderProduct|null $providerProduct */
            $providerProduct = $providerProductMap->get($row->provider_id.'_'.$row->product_id);

            return [
                'provider_id' => (int) $row->provider_id,
                'provider_code' => $row->provider?->code,
                'provider_name' => $row->provider?->name,
                'provider_product_code' => $providerProduct?->provider_product_code,
                'provider_product_name' => $providerProduct?->provider_product_name,
                'base_price' => $basePrice,
                'admin_fee' => $adminFee,
                'commission' => $commission,
                'total_cost' => $basePrice + $adminFee,
            ];
        })->all();

        $ranked = $this->pricingEngine->rankCandidates($candidates, (string) $product->type);
        $selected = $ranked[0];

        $margin = $this->resolveMargin($product->id, (int) $product->category_id, (float) $selected['base_price']);
        $unitPrice = (float) $selected['base_price'] + (float) $selected['admin_fee'] + $margin;
        $finalAmount = $unitPrice * $quantity;

        $orderResult = $this->orderService->create([
            'idempotency_key' => (string) Str::uuid(),
            'quote_token' => null,
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'product_type' => $product->type,
            'quantity' => $quantity,
            'customer_target' => $validated['customer_target'] ?? null,
            'base_price' => (float) $selected['base_price'],
            'admin_fee' => (float) $selected['admin_fee'],
            'margin' => $margin,
            'final_amount' => $finalAmount,
            'selected_provider' => $selected,
            'candidates' => $ranked,
        ]);

        /** @var Order $order */
        $order = $orderResult['order'];

        $payment = $this->paymentService->initiate(
            $order,
            $gateway,
            $validated['method'] ?? null
        );

        $this->rememberRecentOrder($request, (string) $order->order_code);

        return redirect()
            ->route('storefront.track', ['orderCode' => $order->order_code])
            ->with('checkout_summary', [
                'final_amount' => $finalAmount,
                'gateway_reference' => $payment->gateway_reference,
            ]);
    }

    public function history(Request $request): View
    {
        $sessionOrderCodes = collect($request->session()->get('recent_order_codes', []))
            ->filter(static fn ($code): bool => is_string($code) && $code !== '')
            ->values();

        $orders = Order::query()
            ->with([
                'product:id,name,type',
                'payment:id,order_id,gateway,gateway_reference,status,amount,expired_at',
            ])
            ->when($sessionOrderCodes->isNotEmpty(), static fn ($query) => $query->whereIn('order_code', $sessionOrderCodes->all()))
            ->when($sessionOrderCodes->isEmpty(), static fn ($query) => $query->whereRaw('1 = 0'))
            ->orderByDesc('created_at')
            ->get();

        return view('storefront.history', [
            'orders' => $orders,
        ]);
    }

    public function track(string $orderCode): View
    {
        $order = Order::query()
            ->with([
                'product:id,name,type',
                'items:id,order_id,quantity,unit_price,subtotal',
                'payment:id,order_id,gateway,gateway_reference,method,amount,status,paid_at,expired_at,meta,created_at',
                'providerAttempts:id,order_id,provider_id,attempt_no,status,provider_ref,attempted_at',
                'providerAttempts.provider:id,code,name',
            ])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        return view('storefront.track', [
            'order' => $order,
            'paymentMeta' => is_array($order->payment?->meta) ? $order->payment?->meta : [],
        ]);
    }

    private function resolveMargin(int $productId, int $categoryId, float $basePrice): float
    {
        $margin = Margin::query()
            ->where('is_active', true)
            ->where('product_id', $productId)
            ->latest('id')
            ->first();

        if ($margin === null) {
            $margin = Margin::query()
                ->where('is_active', true)
                ->whereNull('product_id')
                ->where('category_id', $categoryId)
                ->latest('id')
                ->first();
        }

        if ($margin === null) {
            return 0;
        }

        $value = (float) $margin->value;

        if (strtoupper((string) $margin->mode) === 'PERCENTAGE') {
            return round(($basePrice * $value) / 100, 2);
        }

        return round($value, 2);
    }

    private function rememberRecentOrder(Request $request, string $orderCode): void
    {
        $existing = $request->session()->get('recent_order_codes', []);

        $recent = collect(is_array($existing) ? $existing : [])
            ->prepend($orderCode)
            ->unique()
            ->take(20)
            ->values()
            ->all();

        $request->session()->put('recent_order_codes', $recent);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function logSecurityEvent(Request $request, string $eventCode, string $severity, int $riskScore, array $context): void
    {
        SecurityEvent::query()->create([
            'event_code' => $eventCode,
            'severity' => $severity,
            'user_id' => auth()->id(),
            'device_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'risk_score' => max(0, min($riskScore, 100)),
            'context' => $context,
            'occurred_at' => now(),
        ]);
    }

    private function resolveGateway(mixed $preferredGateway = null): string
    {
        $configuredGateways = collect(array_keys((array) config('services.payment_gateways', [])))
            ->map(static fn ($code): string => strtoupper((string) $code))
            ->filter(static fn (string $code): bool => $code !== '')
            ->values();

        $activeGatewaySettings = PaymentGatewaySetting::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('code')
            ->get(['code'])
            ->map(static fn (PaymentGatewaySetting $setting): string => strtoupper((string) $setting->code))
            ->filter(static fn (string $code) => $configuredGateways->contains($code))
            ->values();

        $preferred = strtoupper(trim((string) $preferredGateway));
        if ($preferred !== '' && $activeGatewaySettings->contains($preferred)) {
            return $preferred;
        }

        if ($activeGatewaySettings->isNotEmpty()) {
            return (string) $activeGatewaySettings->first();
        }

        $default = strtoupper((string) config('services.payment_default_gateway', 'MIDTRANS'));
        if ($default !== '' && $configuredGateways->contains($default)) {
            return $default;
        }

        return (string) ($configuredGateways->first() ?? 'MIDTRANS');
    }
}
