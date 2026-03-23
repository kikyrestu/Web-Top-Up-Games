<x-layouts.app :title="'TopUp Atlas - Checkout'">
    @php
        $categoryCount = $productsByCategory->count();
        $allProducts = $productsByCategory->flatten(1);
        $productCount = $allProducts->count();
        $quickCategories = $productsByCategory->keys()->take(6);
        $quickProducts = $allProducts->take(8);
        $featuredProducts = $allProducts->take(12);
        $allCategoryNames = $productsByCategory->keys()->values();
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

        .hero-cta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 4px 0 14px;
        }

        .hero-cta .btn-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            border: 1px solid var(--line);
        }

        .hero-cta .btn-main {
            color: #fff;
            background: linear-gradient(120deg, var(--accent), #0ea26a);
            border-color: transparent;
        }

        .hero-cta .btn-sub {
            color: var(--ink);
            background: #fff;
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

        .quick-pill.is-active {
            border-color: transparent;
            background: linear-gradient(120deg, var(--accent), #0ea26a);
            color: #fff;
        }

        .checkout-panel {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
        }

        .market-wrap {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 16px;
            margin-top: 16px;
            align-items: start;
        }

        .catalog-panel {
            background: #fff;
        }

        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 12px;
        }

        .category-tab {
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--ink);
            padding: 7px 12px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .category-tab.is-active {
            border-color: transparent;
            background: linear-gradient(120deg, var(--accent), #0ea26a);
            color: #fff;
        }

        .step-shell {
            display: grid;
            gap: 12px;
        }

        .step-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px;
            background: #fbfcfb;
        }

        .step-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            background: var(--accent);
            color: #fff;
        }

        .step-label {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 10px;
        }

        .product-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            padding: 10px;
            text-align: left;
            cursor: pointer;
            transition: border-color 0.15s ease, transform 0.15s ease;
            position: relative;
        }

        .product-card:hover {
            border-color: var(--accent);
            transform: translateY(-1px);
        }

        .product-card.is-active {
            border-color: transparent;
            background: linear-gradient(120deg, var(--accent), #0ea26a);
            color: #fff;
        }

        .product-name {
            font-size: 13px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .product-thumb {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: 1px solid #d3e5d9;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            background: #f3fbf6;
            color: #1f6f42;
            margin-bottom: 8px;
        }

        .popular-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            border-radius: 999px;
            padding: 3px 7px;
            font-size: 10px;
            font-weight: 800;
            background: #fff2cd;
            color: #7a5600;
            border: 1px solid #f1d487;
        }

        .product-type {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            opacity: 0.9;
        }

        .step-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .gateway-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .gateway-chip {
            border: 1px solid #cde1d5;
            border-radius: 999px;
            padding: 6px 10px;
            background: #f7fbf8;
            font-size: 11px;
            font-weight: 800;
            color: #2f4f3f;
        }

        .selected-product {
            border: 1px solid #cde1d5;
            border-radius: 12px;
            background: #f7fbf8;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .selected-product .cap {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ink-soft);
            font-weight: 800;
        }

        .selected-product .name {
            font-size: 14px;
            font-weight: 800;
            margin-top: 4px;
        }

        .mini-steps {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 10px 0 14px;
        }

        .mini-steps span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px dashed #b9dbc9;
            background: #f3fbf6;
            color: #196d3a;
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 12px;
            font-weight: 700;
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

        .action-row {
            grid-column: 1/-1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 2px;
        }

        .action-hint {
            font-size: 12px;
            color: var(--ink-soft);
        }

        @media (max-width: 920px) {
            .hero-wrap {
                grid-template-columns: 1fr;
            }

            .market-wrap {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 34px;
            }

            .checkout-panel {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .checkout-grid,
            .kpi-row,
            .step-grid,
            .product-grid {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 29px;
            }

            .checkout-panel {
                padding-bottom: 12px;
            }

            .action-row {
                position: sticky;
                bottom: 8px;
                background: #ffffff;
                border: 1px solid var(--line);
                border-radius: 12px;
                padding: 10px;
                box-shadow: 0 10px 24px rgba(19, 34, 26, 0.1);
                margin-top: 8px;
            }

            .action-row .btn {
                width: 100%;
            }

            .action-hint {
                display: none;
            }
        }
    </style>

    <div class="hero-wrap">
        <div class="panel hero-panel">
            <h1 class="hero-title">Top Up & PPOB cepat, rapi, dan aman.</h1>
            <p class="hero-sub">Pilih produk game atau PPOB, checkout dalam satu alur, lalu bayar pakai gateway favoritmu. Cocok untuk transaksi cepat tanpa ribet.</p>

            <div class="hero-cta">
                <a class="btn-link btn-main" href="#checkout-form">Mulai Checkout Sekarang</a>
                <a class="btn-link btn-sub" href="{{ route('public.promo') }}">Lihat Promo Hari Ini</a>
            </div>

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

        <div class="panel" style="background:linear-gradient(135deg, #113424, #1f6f42); color:#fff; border-color:transparent;">
            <h2 style="font-size:26px; margin-bottom:10px;">Format Checkout ala Marketplace Top-Up</h2>
            <p style="margin:0 0 10px; opacity:0.95;">Pilih produk di kiri, data akun di kanan, lanjut bayar. Alurnya sengaja dibuat ringkas seperti platform top-up populer.</p>
            <div class="trust-row">
                <span class="trust-chip" style="background:#ffffff1a; border-color:#ffffff33; color:#fff;">Live Stock Monitoring</span>
                <span class="trust-chip" style="background:#ffffff1a; border-color:#ffffff33; color:#fff;">Secure Checkout</span>
                <span class="trust-chip" style="background:#ffffff1a; border-color:#ffffff33; color:#fff;">Fast Payment Routing</span>
            </div>
        </div>
    </div>

    <div class="market-wrap" id="checkout-form">
        <div class="panel catalog-panel">
            <div class="step-head">
                <span class="step-num">1</span>
                <span class="step-label">Pilih Nominal / Produk</span>
            </div>

            <div class="category-tabs">
                <button type="button" class="category-tab is-active" data-filter-cat="all">Semua</button>
                @foreach ($allCategoryNames as $categoryName)
                    <button type="button" class="category-tab" data-filter-cat="{{ $categoryName }}">{{ $categoryName }}</button>
                @endforeach
            </div>

            <div class="product-grid">
                @foreach ($featuredProducts as $product)
                    @php
                        $thumbRaw = is_array($product->meta ?? null)
                            ? ((string) (($product->meta['thumbnail'] ?? $product->meta['icon'] ?? '') ?: ''))
                            : '';
                        $thumb = $thumbRaw !== '' ? $thumbRaw : strtoupper(substr((string) $product->name, 0, 1));
                        $categoryName = (string) ($product->category?->name ?? 'Lainnya');
                    @endphp
                    <button class="product-card" type="button" data-product-id="{{ (int) $product->id }}" data-category="{{ $categoryName }}">
                        @if ($loop->iteration <= 6)
                            <span class="popular-badge">Popular</span>
                        @endif
                        <span class="product-thumb">{{ $thumb }}</span>
                        <div class="product-name">{{ $product->name }}</div>
                        <div class="product-type">{{ $product->type }}</div>
                    </button>
                @endforeach
            </div>

            <p class="muted" style="margin:6px 0 0; font-size:12px;">Tip: klik kartu produk untuk langsung isi pilihan di form checkout.</p>
        </div>

        <div class="checkout-panel" style="position:sticky; top:16px;">
            <h2>Checkout Instan</h2>
            <p class="muted" style="margin-bottom:12px;">Isi data minimum, sistem akan proses order dan buat payment reference otomatis.</p>

            <div class="mini-steps">
                <span>1. Pilih Produk</span>
                <span>2. Isi Target</span>
                <span>3. Bayar & Selesai</span>
            </div>

            <form method="post" action="{{ route('storefront.checkout') }}" class="step-shell">
                @csrf

                <section class="step-card">
                    <div class="selected-product">
                        <div class="cap">Produk Dipilih</div>
                        <div class="name" id="selected-product-name">Belum dipilih</div>
                    </div>

                    <label for="product_id">Daftar produk lengkap</label>
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
                </section>

                <section class="step-card">
                    <div class="step-head">
                        <span class="step-num">2</span>
                        <span class="step-label">Masukkan Data Akun</span>
                    </div>

                    <div class="step-grid">
                        <div>
                            <label for="customer_target">Target Customer</label>
                            <input id="customer_target" name="customer_target" type="text" value="{{ old('customer_target') }}" placeholder="User ID / Phone / Meter Number">
                        </div>

                        <div>
                            <label for="quantity">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="10" value="{{ old('quantity', 1) }}">
                        </div>
                    </div>
                </section>

                <section class="step-card">
                    <div class="step-head">
                        <span class="step-num">3</span>
                        <span class="step-label">Pilih Pembayaran & Konfirmasi</span>
                    </div>

                    <div class="step-grid">
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
                    </div>

                    <div class="gateway-row">
                        @foreach ($gateways as $gateway)
                            <span class="gateway-chip">{{ $gateway }}</span>
                        @endforeach
                    </div>
                </section>

                @if (session('checkout_challenge_question'))
                    <div style="grid-column:1/-1; border:1px solid var(--line); border-radius:12px; padding:12px; background:#fff9e6;">
                        <label for="security_challenge_answer">Verifikasi Keamanan</label>
                        <p class="muted" style="margin:6px 0 10px;">{{ session('checkout_challenge_question') }}</p>
                        <input id="security_challenge_answer" name="security_challenge_answer" type="text" value="{{ old('security_challenge_answer') }}" placeholder="Masukkan jawaban challenge">
                    </div>
                @endif

                <div class="action-row">
                    <span class="action-hint">Order diproses otomatis setelah payment reference terbentuk.</span>
                    <button class="btn" type="submit">Buat Order + Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productSelect = document.getElementById('product_id');
            const quickButtons = document.querySelectorAll('[data-product-id]');
            const categoryTabs = document.querySelectorAll('[data-filter-cat]');
            const selectedProductName = document.getElementById('selected-product-name');

            function setActiveProduct(productId) {
                quickButtons.forEach(function (node) {
                    const nodeId = String(node.getAttribute('data-product-id') || '');
                    node.classList.toggle('is-active', nodeId === productId && productId !== '');
                });

                if (selectedProductName && productSelect) {
                    const selectedText = productSelect.options[productSelect.selectedIndex]?.text || 'Belum dipilih';
                    selectedProductName.textContent = selectedText;
                }
            }

            if (productSelect) {
                setActiveProduct(String(productSelect.value || ''));
                productSelect.addEventListener('change', function () {
                    setActiveProduct(String(productSelect.value || ''));
                });
            }

            categoryTabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const targetCategory = String(tab.getAttribute('data-filter-cat') || 'all');

                    categoryTabs.forEach(function (node) {
                        node.classList.toggle('is-active', node === tab);
                    });

                    quickButtons.forEach(function (btn) {
                        if (!btn.classList.contains('product-card')) {
                            return;
                        }

                        const cardCategory = String(btn.getAttribute('data-category') || '');
                        const visible = targetCategory === 'all' || cardCategory === targetCategory;
                        btn.style.display = visible ? '' : 'none';
                    });
                });
            });

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
