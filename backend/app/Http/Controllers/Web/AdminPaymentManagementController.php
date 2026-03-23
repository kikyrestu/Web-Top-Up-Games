<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentGatewaySetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AdminPaymentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = strtoupper(trim((string) $request->query('status', '')));
        $search = trim((string) $request->query('q', ''));

        $rows = PaymentGatewaySetting::query()
            ->when($status === 'ACTIVE', static fn ($query) => $query->where('is_active', true))
            ->when($status === 'INACTIVE', static fn ($query) => $query->where('is_active', false))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('display_name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('priority')
            ->orderBy('code')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payment.gateways-index', [
            'rows' => $rows,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.payment.gateways-form', [
            'row' => new PaymentGatewaySetting(),
            'formMode' => 'create',
            'availableCodes' => $this->availableGatewayCodes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        PaymentGatewaySetting::query()->create($data);

        return redirect()->route('admin.payment.gateways.index')->with('notice', 'Gateway setting berhasil dibuat.');
    }

    public function edit(PaymentGatewaySetting $gateway): View
    {
        return view('admin.payment.gateways-form', [
            'row' => $gateway,
            'formMode' => 'edit',
            'availableCodes' => $this->availableGatewayCodes(),
        ]);
    }

    public function update(Request $request, PaymentGatewaySetting $gateway): RedirectResponse
    {
        $data = $this->validateData($request, $gateway->id);
        $gateway->update($data);

        return redirect()->route('admin.payment.gateways.index')->with('notice', 'Gateway setting berhasil diperbarui.');
    }

    public function destroy(PaymentGatewaySetting $gateway): RedirectResponse
    {
        $gateway->delete();

        return redirect()->route('admin.payment.gateways.index')->with('notice', 'Gateway setting berhasil dihapus.');
    }

    /**
     * @return array<int, string>
     */
    private function availableGatewayCodes(): array
    {
        return collect(array_keys((array) config('services.payment_gateways', [])))
            ->map(static fn ($code): string => strtoupper((string) $code))
            ->filter(static fn (string $code): bool => $code !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?int $gatewayId = null): array
    {
        $availableCodes = $this->availableGatewayCodes();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::in($availableCodes),
                Rule::unique('payment_gateway_settings', 'code')->ignore($gatewayId),
            ],
            'display_name' => ['required', 'string', 'max:120'],
            'priority' => ['required', 'integer', 'min:1', 'max:9999'],
            'fee_flat' => ['nullable', 'numeric', 'min:0'],
            'fee_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'supported_methods_text' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $methods = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['supported_methods_text'] ?? '')))
            ->map(static fn ($value): string => strtoupper(trim((string) $value)))
            ->filter(static fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();

        return [
            'code' => strtoupper((string) $validated['code']),
            'display_name' => trim((string) $validated['display_name']),
            'is_active' => $request->boolean('is_active'),
            'priority' => (int) $validated['priority'],
            'fee_flat' => (float) ($validated['fee_flat'] ?? 0),
            'fee_percent' => (float) ($validated['fee_percent'] ?? 0),
            'supported_methods' => $methods !== [] ? $methods : null,
            'notes' => trim((string) ($validated['notes'] ?? '')) ?: null,
        ];
    }
}
