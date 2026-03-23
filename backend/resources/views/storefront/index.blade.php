<x-layouts.app :title="'TopUp Atlas - Checkout'">
    @php
        $categoryCount = $productsByCategory->count();
        $allProducts = $productsByCategory->flatten(1);
        $productCount = $allProducts->count();
        $quickCategories = $productsByCategory->keys()->take(6);
        $quickProducts = $allProducts->take(8);
    @endphp

    <style>
        .hero-wrap {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            gap: 16px;
        }

        .hero-panel {
            background:
                radial-gradient(circle at 8% 12%, rgba(15, 123, 82, 0.16), transparent 40%),
                radial-gradient(circle at 88% 10%, rgba(255, 209, 102, 0.28), transparent 35%),
                #fff;
        }

        .hero-title {
            font-size: 42px;
            line-height: 1.04;
            margin-bottom: 12px;
        }

        .hero-sub {
            color: var(--ink-soft);
            font-size: 15px;
            max-width: 60ch;
            margin-bottom: 16px;
        }

        .kpi-row {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin: 12px 0 16px;
        }

        .kpi {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #f8fbf7;
            padding: 12px;
        }

        .kpi .n {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .kpi .l {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--ink-soft);
        }

        .tag-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0;
        }

        .quick-pill {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 12px;
            background: #fff;
            font-size: 12px;
            font-weight: 700;
            color: var(--ink);
            cursor: pointer;
        }

        .quick-pill:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .checkout-panel {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
        }

        .checkout-panel h2 {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .trust-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .trust-chip {
            border: 1px solid #b9dbc9;
            background: #edf9f1;
            color: #196d3a;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 700;
        }

        @media (max-width: 920px) {
            .hero-wrap {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 34px;
            }
        }

        @media (max-width: 640px) {
            .checkout-grid,
            .kpi-row {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 29px;
            }
        }
    </style>

    <div class="hero-wrap">
        <div class="panel hero-panel">
            <h1 class="hero-title">Top Up & PPOB cepat, rapi, dan aman.</h1>
            <p class="hero-sub">Pilih produk game atau PPOB, checkout dalam satu alur, lalu bayar pakai gateway favoritmu. Cocok untuk transaksi cepat tanpa ribet.</p>

            <div class="kpi-row">
                <div class="kpi">
                    <div class="n">{{ $productCount }}</div>
                    <div class="l">Produk Aktif</div>
                </div>
                <div class="kpi">
                    <div class="n">{{ $categoryCount }}</div>
                    <div class="l">Kategori</div>
                </div>
                <div class="kpi">
                    <div class="n">24/7</div>
                    <div class="l">Order Online</div>
                </div>
            </div>

            <div class="trust-row">
                <span class="trust-chip">Harga Kompetitif</span>
                <span class="trust-chip">Verifikasi Keamanan</span>
                <span class="trust-chip">Tracking Order Real-time</span>
            </div>

            <h3 style="margin-top:16px;">Kategori Populer</h3>
            <div class="tag-row">
                @foreach ($quickCategories as $categoryName)
                    <span class="quick-pill">{{ $categoryName }}</span>
                @endforeach
            </div>

            <h3 style="margin-top:14px;">Produk Cepat Pilih</h3>
            <div class="tag-row">
                @foreach ($quickProducts as $product)
                    <button class="quick-pill" type="button" data-product-id="{{ (int) $product->id }}">{{ $product->name }}</button>
                @endforeach
            </div>
        </div>

        <div class="checkout-panel">
            <h2>Checkout Instan</h2>
            <p class="muted" style="margin-bottom:12px;">Isi data minimum, sistem akan proses order dan buat payment reference otomatis.</p>

            <form method="post" action="{{ route('storefront.checkout') }}" class="checkout-grid">
                @csrf

                <div style="grid-column:1/-1;">
                    <label for="product_id">Produk</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Pilih produk</option>
                        @foreach ($productsByCategory as $category => $products)
                            <optgroup label="{{ $category }}">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                        {{ $product->name }} ({{ $product->type }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="customer_target">Target Customer</label>
                    <input id="customer_target" name="customer_target" type="text" value="{{ old('customer_target') }}" placeholder="User ID / Phone / Meter Number">
                </div>

                <div>
                    <label for="quantity">Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="10" value="{{ old('quantity', 1) }}">
                </div>

                <div>
                    <label for="gateway">Payment Gateway</label>
                    <select id="gateway" name="gateway" required>
                        @foreach ($gateways as $gateway)
                            <option value="{{ $gateway }}" @selected(old('gateway') === $gateway)>{{ $gateway }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="method">Metode Payment (opsional)</label>
                    <input id="method" name="method" type="text" value="{{ old('method') }}" placeholder="VA / QRIS / E-Wallet">
                </div>

                @if (session('checkout_challenge_question'))
                    <div style="grid-column:1/-1; border:1px solid var(--line); border-radius:12px; padding:12px; background:#fff9e6;">
                        <label for="security_challenge_answer">Verifikasi Keamanan</label>
                        <p class="muted" style="margin:6px 0 10px;">{{ session('checkout_challenge_question') }}</p>
                        <input id="security_challenge_answer" name="security_challenge_answer" type="text" value="{{ old('security_challenge_answer') }}" placeholder="Masukkan jawaban challenge">
                    </div>
                @endif

                <div style="grid-column:1/-1; display:flex; justify-content:flex-end;">
                    <button class="btn" type="submit">Buat Order + Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productSelect = document.getElementById('product_id');
            const quickButtons = document.querySelectorAll('[data-product-id]');

            quickButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!productSelect) {
                        return;
                    }

                    productSelect.value = String(button.getAttribute('data-product-id') || '');
                    productSelect.dispatchEvent(new Event('change'));
                    productSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
        });
    </script>
</x-layouts.app>
