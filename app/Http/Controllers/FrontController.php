<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = trim((string) $request->query('q', ''));

        // Setup Banners (hero position only for main slider)
        $banners = Banner::where('is_active', true)->where('position', 'hero')->orderBy('order')->get();

        // PPOB Promo Banner (left card in section)
        $ppobPromoBanner = Banner::where('is_active', true)->where('position', 'ppob_promo')->first();

        // Game Popular
        $popularGames = Category::where('is_active', true)
                                ->gameTypes()
                                ->where('is_popular', true)
                                ->orderBy('sort_order')
                                ->get();

        // PPOB categories for tabs (non-game types)
        $ppobCategories = Category::where('is_active', true)
            ->nonGameTypes()
            ->orderBy('sort_order')
            ->get();

        // Semua Game
        $allGamesQuery = Category::where('is_active', true)
                            ->gameTypes()
                            ->orderBy('sort_order');
                            
        if ($searchQuery !== '') {
            $allGamesQuery->where('name', 'like', '%' . $searchQuery . '%');
        }
        $allGames = $allGamesQuery->get();

        $searchCategories = collect();
        $searchProducts = collect();
        if ($searchQuery !== '') {
            $searchCategories = Category::where('is_active', true)
                ->where('name', 'like', '%' . $searchQuery . '%')
                ->orderBy('sort_order')
                ->limit(8)
                ->get();

            $searchProducts = Product::with(['category', 'providerMappings'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%' . $searchQuery . '%')
                        ->orWhereHas('providerMappings', function ($mappingQuery) use ($searchQuery) {
                            $mappingQuery->where('provider_product_code', 'like', '%' . $searchQuery . '%');
                        });
                })
                ->whereHas('category', function ($q) {
                    $q->where('is_active', true);
                })
                ->orderBy('price_sell')
                ->limit(12)
                ->get();
        }

        // Kategori By Type
        $selulerGames = Category::where('is_active', true)->where('type', 'seluler')->orderBy('sort_order')->get();
        $pcGames = Category::where('is_active', true)->where('type', 'pc')->orderBy('sort_order')->get();
        $voucherGames = Category::where('is_active', true)->where('type', 'voucher')->orderBy('sort_order')->get();

        // Promo / Artikel terbaru
        $latestArticles = Article::where('is_published', true)->orderBy('created_at', 'desc')->take(3)->get();

        return view('front.index', compact(
            'banners',
            'ppobPromoBanner',
            'ppobCategories',
            'popularGames',
            'allGames',
            'selulerGames',
            'pcGames',
            'voucherGames',
            'latestArticles',
            'searchQuery',
            'searchCategories',
            'searchProducts'
        ));
    }

    /**
     * Top Up Game landing page — all game subcategories
     */
    public function topUpGame()
    {
        $categories = Category::where('is_active', true)
            ->gameTypes()
            ->orderBy('sort_order')
            ->get();

        $grouped = $categories->groupBy('type');

        return view('front.top-up-game', compact('categories', 'grouped'));
    }

    /**
     * PPOB landing page — all PPOB/pulsa subcategories
     */
    public function ppob()
    {
        $categories = Category::where('is_active', true)
            ->nonGameTypes()
            ->orderBy('sort_order')
            ->get();

        $grouped = $categories->groupBy('type');

        return view('front.ppob', compact('categories', 'grouped'));
    }

    public function showCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category && ctype_digit((string) $slug)) {
            $category = Category::where('id', (int) $slug)
                ->where('is_active', true)
                ->first();

            if ($category && filled($category->slug)) {
                return redirect()->route('front.category', $category->slug, 301);
            }
        }

        if (!$category) {
            abort(404);
        }

        $products = Product::with('providerMappings')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('price_sell')
            ->get();

        $preselectedProductId = (int) $request->query('product', 0);
        if ($preselectedProductId > 0) {
            $existsInCategory = $products->contains(fn($product) => (int) $product->id === $preselectedProductId);
            if (! $existsInCategory) {
                $preselectedProductId = 0;
            }
        }

        $paymentGateways = $this->mapPaymentGatewaysForCustomer(
            PaymentGateway::where('is_active', true)->get()
        );
        $paymentChannels = $this->buildPaymentChannelOptions($paymentGateways);
        $formFields = $category->getFormFields();

        // Route to correct view based on category type
        $viewName = Category::isGameType($category->type)
            ? 'front.show-game'
            : 'front.show-ppob';

        return view($viewName, compact('category', 'products', 'paymentGateways', 'paymentChannels', 'formFields', 'preselectedProductId'));
    }

    public function checkout(Request $request)
    {
        // GET /checkout has no direct page — redirect to home
        return redirect()->route('front.index');
    }

    public function cekPesanan()
    {
        return view('front.cek-pesanan');
    }

    public function prosesCekPesanan(Request $request)
    {
        $request->validate([
            'search_type' => 'required|in:invoice,target',
            'search_value' => 'required|string',
        ]);

        $query = Transaction::query();

        if ($request->search_type === 'invoice') {
            $query->where('invoice_number', $request->search_value);
        } else {
            $query->where('target', $request->search_value);
        }

        $transaction = $query->first();

        if (!$transaction) {
            return back()->with('error', 'Pesanan tidak ditemukan. Pastikan data yang dimasukkan benar.');
        }

        return view('front.cek-pesanan', compact('transaction'));
    }

    public function page(string $slug)
    {
        $pages = [
            'daftar-harga' => [
                'title' => 'Daftar Harga Layanan',
                'description' => 'Lihat daftar harga top up game dan layanan PPOB terbaru dengan update berkala.',
                'heading' => 'Daftar Harga Layanan',
                'content' => [
                    'Harga produk diperbarui secara berkala mengikuti harga provider.',
                    'Untuk daftar harga terlengkap, gunakan fitur pencarian produk di beranda.',
                    'Semua harga yang tampil saat checkout adalah harga final sebelum pembayaran.',
                ],
            ],
            'faq' => [
                'title' => 'FAQ Top Up dan Pembayaran',
                'description' => 'Pertanyaan yang sering diajukan seputar top up, pembayaran, dan status transaksi.',
                'heading' => 'Pertanyaan Umum (FAQ)',
                'content' => [
                    'Pesanan umumnya diproses otomatis dalam hitungan detik hingga menit.',
                    'Jika pesanan belum masuk, cek kembali status pada halaman Cek Pesanan.',
                    'Pastikan ID tujuan, nominal, dan metode pembayaran sudah benar sebelum bayar.',
                ],
                'faq_items' => [
                    [
                        'question' => 'Berapa lama proses top up setelah pembayaran berhasil?',
                        'answer' => 'Sebagian besar transaksi diproses otomatis dalam hitungan detik hingga beberapa menit tergantung provider.',
                    ],
                    [
                        'question' => 'Bagaimana jika status pesanan belum berubah?',
                        'answer' => 'Silakan cek halaman Cek Pesanan menggunakan nomor invoice. Jika masih pending terlalu lama, hubungi customer support dengan menyertakan invoice.',
                    ],
                    [
                        'question' => 'Apakah data tujuan bisa diubah setelah pembayaran?',
                        'answer' => 'Data tujuan yang sudah dibayar umumnya tidak dapat diubah, jadi pastikan input ID/nomor sudah benar sebelum checkout.',
                    ],
                ],
            ],
            'kontak' => [
                'title' => 'Hubungi Customer Support',
                'description' => 'Hubungi tim customer support untuk bantuan transaksi, komplain, dan pertanyaan layanan.',
                'heading' => 'Hubungi Customer Support',
                'content' => [
                    'Tim dukungan siap membantu kendala transaksi dan pembayaran Anda.',
                    'Silakan kirim nomor invoice agar proses pengecekan lebih cepat.',
                    'Gunakan kanal resmi yang tersedia untuk keamanan komunikasi.',
                ],
            ],
            'syarat-ketentuan' => [
                'title' => 'Syarat dan Ketentuan',
                'description' => 'Ketentuan penggunaan layanan top up game dan PPOB yang berlaku di website ini.',
                'heading' => 'Syarat dan Ketentuan',
                'content' => [
                    'Dengan menggunakan layanan, pengguna dianggap telah membaca dan menyetujui ketentuan ini.',
                    'Kesalahan input data tujuan oleh pengguna di luar tanggung jawab sistem.',
                    'Kami berhak melakukan pembaruan kebijakan tanpa pemberitahuan terpisah.',
                ],
            ],
            'kebijakan-privasi' => [
                'title' => 'Kebijakan Privasi',
                'description' => 'Informasi pengelolaan data pengguna dan perlindungan privasi pada layanan kami.',
                'heading' => 'Kebijakan Privasi',
                'content' => [
                    'Data yang dikumpulkan digunakan untuk pemrosesan transaksi dan peningkatan layanan.',
                    'Kami tidak memperjualbelikan data pribadi pengguna kepada pihak ketiga.',
                    'Pengguna dapat menghubungi customer support untuk pertanyaan terkait privasi data.',
                ],
            ],
        ];

        if (!isset($pages[$slug])) {
            abort(404);
        }

        $page = $pages[$slug];
        $contactWhatsapp = Setting::get('contact_whatsapp');
        $contactEmail = Setting::get('contact_email');

        return view('front.page', compact('slug', 'page', 'contactWhatsapp', 'contactEmail'));
    }

    private function mapPaymentGatewaysForCustomer($gateways)
    {
        return $gateways->map(function ($gateway) {
            [$displayName, $fallbackType, $methods, $channels] = $this->resolveGatewayPresentation((string) $gateway->code, (string) $gateway->name);

            $gateway->setAttribute('display_name', $displayName);
            $gateway->setAttribute('customer_methods', $methods);
            $gateway->setAttribute('customer_channels', $channels);

            if (empty($gateway->type)) {
                $gateway->setAttribute('type', $fallbackType);
            }

            return $gateway;
        });
    }

    private function resolveGatewayPresentation(string $code, string $name): array
    {
        $normalizedCode = strtolower(trim($code));

        if (in_array($normalizedCode, ['doku', 'dompetx'], true)) {
            return [
                'QRIS / E-Wallet / Virtual Account / Retail',
                'otomatis',
                ['QRIS', 'E-Wallet', 'Virtual Account', 'Alfamart/Indomaret'],
                [
                    'e_wallet' => ['DANA', 'ShopeePay', 'GoPay', 'GoPay Token', 'OVO', 'LinkAja', 'Jenius Pay', 'Sakuku', 'Doku Wallet'],
                    'transfer_bank' => ['BCA Virtual Account', 'BNI Virtual Account', 'BRI Virtual Account', 'Permata Virtual Account', 'CIMB Virtual Account'],
                    'qris' => ['QRIS'],
                    'otc_non_bank' => ['Alfamart', 'Indomaret'],
                    'perbankan_online' => ['Perbankan Online'],
                    'kartu_debit_kredit' => ['Kartu Debit / Kredit'],
                ],
            ];
        }

        if ($normalizedCode === 'midtrans') {
            return [
                'QRIS / E-Wallet / Virtual Account / Retail',
                'otomatis',
                ['QRIS', 'GoPay/ShopeePay', 'Virtual Account', 'Alfamart/Indomaret'],
                [
                    'e_wallet' => ['GoPay', 'ShopeePay'],
                    'transfer_bank' => ['BCA Virtual Account', 'BNI Virtual Account', 'BRI Virtual Account', 'Permata Virtual Account'],
                    'qris' => ['QRIS'],
                    'otc_non_bank' => ['Alfamart', 'Indomaret'],
                    'kartu_debit_kredit' => ['Kartu Debit / Kredit'],
                ],
            ];
        }

        if ($normalizedCode === 'klikqris') {
            return [
                'QRIS',
                'qris',
                ['QRIS'],
                [
                    'qris' => ['QRIS'],
                ],
            ];
        }

        return [
            $name,
            'other',
            [$name],
            [
                'other' => [$name],
            ],
        ];
    }

    private function buildPaymentChannelOptions($gateways)
    {
        $result = [];
        $seen = [];

        foreach ($gateways as $gateway) {
            $channels = (array) ($gateway->customer_channels ?? []);
            foreach ($channels as $groupKey => $items) {
                foreach ((array) $items as $channelName) {
                    $dedupeKey = strtolower($groupKey . '|' . $gateway->id . '|' . $channelName);
                    if (isset($seen[$dedupeKey])) {
                        continue;
                    }
                    $seen[$dedupeKey] = true;
                    $result[] = [
                        'group_key' => (string) $groupKey,
                        'group_label' => $this->channelGroupLabel((string) $groupKey),
                        'name' => (string) $channelName,
                        'gateway_id' => (int) $gateway->id,
                        'provider' => (string) $gateway->name,
                    ];
                }
            }
        }

        return collect($result);
    }

    private function channelGroupLabel(string $groupKey): string
    {
        $labels = [
            'e_wallet' => 'E-wallet',
            'transfer_bank' => 'Transfer Bank',
            'qris' => 'QRIS',
            'otc_non_bank' => 'OTC non-Bank',
            'perbankan_online' => 'Perbankan online',
            'kartu_debit_kredit' => 'Kartu Debit / Kredit',
            'voucher_fisik' => 'Voucher Fisik',
            'sms_seluler' => 'SMS & Seluler',
            'other' => 'Lainnya',
        ];

        return $labels[$groupKey] ?? ucwords(str_replace('_', ' ', $groupKey));
    }
}

