<x-layouts.app :title="'TopUp Atlas - Instant Top Up'">
    @php
        $allProducts = $productsByCategory->flatten(1)->values();
        $popularProducts = $allProducts->take(12);
        $categorySections = $productsByCategory->take(5);
        $faqItems = [
            ['q' => 'Voucher UniPin di Indonesia berlaku untuk apa?', 'a' => 'Voucher dapat dipakai untuk pembelian game item, top up, dan produk digital pada katalog aktif.'],
            ['q' => 'Tidak bisa menemukan metode bayar favorit?', 'a' => 'Pilih gateway lain terlebih dulu, kemudian isi metode pada form checkout cepat untuk preferensi pembayaranmu.'],
            ['q' => 'Saran jika saldo e-wallet terpotong tapi status belum selesai?', 'a' => 'Gunakan menu Cek Transaksi, lalu kirim order code ke dukungan pelanggan agar ditindaklanjuti cepat.'],
            ['q' => 'Bagaimana proses refund?', 'a' => 'Refund mengikuti status transaksi dari provider dan gateway. Tim dukungan akan konfirmasi setelah verifikasi.'],
        ];
    @endphp

    <style>
        .dark-page {
            color: #e8eefb;
        }

        .dark-page .panel {
            background: #152440;
            border: 1px solid #2a3d62;
            box-shadow: none;
        }

        .section-title {
            margin: 0 0 12px;
            color: #f2f6ff;
            font-size: 25px;
        }

        .hero-slider {
            margin-top: 8px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #2f4870;
            position: relative;
        }

        .hero-track {
            display: flex;
            transition: transform .5s ease;
        }

        .hero-slide {
            min-width: 100%;
            padding: 24px;
            min-height: 220px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 10px;
        }

        .hero-slide h1 {
            margin: 0;
            font-size: 32px;
            line-height: 1.05;
            color: #fff;
        }

        .hero-slide p {
            margin: 0;
            font-size: 14px;
            color: #dce8ff;
            max-width: 56ch;
        }

        .hero-slide a {
            display: inline-flex;
            width: fit-content;
            text-decoration: none;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 800;
            color: #fff;
            background: #f09c2b;
        }

        .hero-a { background: linear-gradient(130deg, #0e1d38, #0f2857 55%, #304bb9); }
        .hero-b { background: linear-gradient(130deg, #102e2e, #16634a 55%, #33a16f); }
        .hero-c { background: linear-gradient(130deg, #301f10, #8b4a1e 55%, #d07b28); }

        .hero-dots {
            position: absolute;
            right: 12px;
            bottom: 12px;
            display: flex;
            gap: 7px;
        }

        .hero-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            border: 1px solid #ffffff8a;
            background: #ffffff4f;
            cursor: pointer;
        }

        .hero-dot.is-active {
            background: #f09c2b;
            border-color: #f09c2b;
        }

        .product-rail {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
        }

        .item-card {
            border-radius: 12px;
            border: 1px solid #35517d;
            background: #0f1f39;
            padding: 8px;
            display: grid;
            gap: 8px;
            cursor: pointer;
            text-align: left;
            color: #f4f7ff;
        }

        .item-card:hover {
            border-color: #6a8fcb;
        }

        .item-thumb {
            width: 100%;
            aspect-ratio: 4 / 5;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #2e466f;
            background: linear-gradient(130deg, #253f69, #365d97);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
        }

        .item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-name {
            font-size: 12px;
            font-weight: 800;
            line-height: 1.3;
            min-height: 30px;
        }

        .item-meta {
            font-size: 11px;
            color: #9bb3d7;
        }

        .item-price {
            font-size: 12px;
            font-weight: 800;
            color: #ffd38f;
        }

        .benefit-strip {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .benefit-card {
            border: 1px solid #2a3f62;
            border-radius: 12px;
            background: #182947;
            padding: 12px;
            display: grid;
            gap: 4px;
        }

        .benefit-card .title {
            font-size: 13px;
            font-weight: 800;
            color: #fff;
        }

        .benefit-card .desc {
            font-size: 12px;
            color: #a7bbdb;
        }

        .section-block {
            margin-top: 14px;
            border-radius: 14px;
            border: 1px solid #2a3f62;
            background: #152440;
            padding: 12px;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .section-head h3 {
            margin: 0;
            font-size: 18px;
            color: #fff;
        }

        .section-head a {
            font-size: 11px;
            text-decoration: none;
            border: 1px solid #446695;
            color: #d6e7ff;
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 800;
        }

        .quick-checkout {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 12px;
        }

        .checkout-box {
            border: 1px solid #2d446b;
            border-radius: 14px;
            background: #13213a;
            padding: 12px;
        }

        .checkout-box h3 {
            margin: 0 0 8px;
            color: #fff;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .checkout-grid label {
            display: block;
            font-size: 12px;
            color: #bdd0ed;
            margin-bottom: 4px;
            font-weight: 700;
        }

        .checkout-grid input,
        .checkout-grid select {
            width: 100%;
            border: 1px solid #385278;
            border-radius: 10px;
            background: #0f1c32;
            color: #ebf3ff;
            padding: 10px;
            font-size: 13px;
        }

        .estimate {
            border: 1px solid #385278;
            border-radius: 10px;
            background: #0f1d34;
            padding: 10px;
            display: grid;
            gap: 6px;
        }

        .estimate-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            font-size: 12px;
            color: #c9daf3;
        }

        .estimate-total {
            border-top: 1px dashed #385278;
            margin-top: 4px;
            padding-top: 7px;
            font-weight: 800;
            color: #ffd38f;
        }

        .checkout-submit {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .checkout-submit button {
            border: none;
            border-radius: 10px;
            background: linear-gradient(120deg, #f39f34, #e36f27);
            color: #fff;
            font-weight: 800;
            padding: 10px 14px;
            cursor: pointer;
        }

        .promo-grid {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .promo-tile {
            border-radius: 12px;
            border: 1px solid #36537d;
            padding: 10px;
            color: #fff;
            text-decoration: none;
            min-height: 120px;
            display: grid;
            gap: 6px;
            align-content: end;
        }

        .promo-tile small {
            color: #d8e8ff;
            opacity: .95;
        }

        .p1 { background: linear-gradient(130deg, #202f7a, #464fd1); }
        .p2 { background: linear-gradient(130deg, #1e5f3f, #2d9e6a); }
        .p3 { background: linear-gradient(130deg, #8a3c14, #ce7b30); }
        .p4 { background: linear-gradient(130deg, #5a1d6f, #a03bc3); }

        .faq-wrap,
        .support-wrap {
            margin-top: 14px;
            border: 1px solid #2a3f62;
            border-radius: 14px;
            background: #152440;
            padding: 12px;
        }

        .faq-wrap details {
            border-top: 1px solid #2b4369;
            padding: 9px 0;
        }

        .faq-wrap details:first-of-type {
            border-top: none;
            padding-top: 0;
        }

        .faq-wrap summary {
            cursor: pointer;
            color: #f4f7ff;
            font-size: 13px;
            font-weight: 800;
        }

        .faq-wrap p {
            margin: 8px 0 0;
            color: #b5c9e8;
            font-size: 12px;
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .support-item {
            border: 1px solid #36537d;
            border-radius: 10px;
            background: #10203a;
            padding: 9px;
            text-align: center;
            font-size: 12px;
            color: #d6e6ff;
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            .product-rail {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .promo-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .hero-slide {
                min-height: 190px;
                padding: 16px;
            }

            .hero-slide h1 {
                font-size: 25px;
            }

            .product-rail {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .benefit-strip {
                grid-template-columns: 1fr;
            }

            .quick-checkout {
                grid-template-columns: 1fr;
            }

            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .support-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <div class="dark-page">
        <section class="hero-slider">
            <div class="hero-track" id="hero-track">
                <article class="hero-slide hero-a">
                    <h1>Ramadan Taktis: Top Up Lebih Cepat & Aman</h1>
                    <p>Pilih produk favoritmu, lanjut checkout, dan pantau transaksi real-time langsung dari satu halaman.</p>
                    <a href="#quick-checkout">Baca Selengkapnya</a>
                </article>
                <article class="hero-slide hero-b">
                    <h1>Game Populer & PPOB Dalam Satu Platform</h1>
                    <p>Top up game, bayar tagihan, dan beli voucher digital dalam pengalaman marketplace dark-style yang ringkas.</p>
                    <a href="#catalog-area">Lihat Katalog</a>
                </article>
                <article class="hero-slide hero-c">
                    <h1>Diskon Harian Untuk Produk Favoritmu</h1>
                    <p>Manfaatkan promo event mingguan dengan metode pembayaran yang paling nyaman untukmu.</p>
                    <a href="{{ route('public.promo') }}">Lihat Promo</a>
                </article>
            </div>
            <div class="hero-dots">
                <button class="hero-dot is-active" type="button" data-hero-slide="0" aria-label="Slide 1"></button>
                <button class="hero-dot" type="button" data-hero-slide="1" aria-label="Slide 2"></button>
                <button class="hero-dot" type="button" data-hero-slide="2" aria-label="Slide 3"></button>
            </div>
        </section>

        <section class="section-block" id="catalog-area">
            <div class="section-head">
                <h3>Game Populer</h3>
                <a href="#quick-checkout">Top Up Sekarang</a>
            </div>
            <div class="product-rail">
                @foreach ($popularProducts as $product)
                    @php
                        $thumbRaw = is_array($product->meta ?? null) ? ((string) (($product->meta['thumbnail'] ?? $product->meta['icon'] ?? '') ?: '')) : '';
                        $thumbIsImage = $thumbRaw !== '' && (str_starts_with($thumbRaw, 'http://') || str_starts_with($thumbRaw, 'https://') || str_starts_with($thumbRaw, '/'));
                        $thumb = $thumbRaw !== '' ? $thumbRaw : strtoupper(substr((string) $product->name, 0, 1));
                        $startPrice = (float) ($startingPrices[$product->id] ?? 0);
                    @endphp
                    <button class="item-card" type="button" data-product-id="{{ (int) $product->id }}">
                        <span class="item-thumb">
                            @if ($thumbIsImage)
                                <img src="{{ $thumb }}" alt="{{ $product->name }}">
                            @else
                                {{ $thumb }}
                            @endif
                        </span>
                        <span class="item-name">{{ $product->name }}</span>
                        <span class="item-meta">{{ $product->type }}</span>
                        <span class="item-price">{{ $startPrice > 0 ? 'Mulai Rp '.number_format($startPrice, 0, ',', '.') : 'Harga dinamis' }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="benefit-strip">
            <article class="benefit-card">
                <span class="title">Isi ulang instan</span>
                <span class="desc">Order diproses otomatis dan cepat setelah pembayaran terkonfirmasi.</span>
            </article>
            <article class="benefit-card">
                <span class="title">Hadiah besar</span>
                <span class="desc">Promo acak harian untuk transaksi pada kategori game terpilih.</span>
            </article>
            <article class="benefit-card">
                <span class="title">Terpercaya</span>
                <span class="desc">Jalur pembayaran aman dengan pemantauan event keamanan checkout.</span>
            </article>
        </section>

        @foreach ($categorySections as $categoryName => $products)
            <section class="section-block">
                <div class="section-head">
                    <h3>{{ $categoryName }}</h3>
                    <a href="#quick-checkout">Lainnya</a>
                </div>
                <div class="product-rail">
                    @foreach ($products->take(6) as $product)
                        @php
                            $thumbRaw = is_array($product->meta ?? null) ? ((string) (($product->meta['thumbnail'] ?? $product->meta['icon'] ?? '') ?: '')) : '';
                            $thumbIsImage = $thumbRaw !== '' && (str_starts_with($thumbRaw, 'http://') || str_starts_with($thumbRaw, 'https://') || str_starts_with($thumbRaw, '/'));
                            $thumb = $thumbRaw !== '' ? $thumbRaw : strtoupper(substr((string) $product->name, 0, 1));
                            $startPrice = (float) ($startingPrices[$product->id] ?? 0);
                        @endphp
                        <button class="item-card" type="button" data-product-id="{{ (int) $product->id }}">
                            <span class="item-thumb">
                                @if ($thumbIsImage)
                                    <img src="{{ $thumb }}" alt="{{ $product->name }}">
                                @else
                                    {{ $thumb }}
                                @endif
                            </span>
                            <span class="item-name">{{ $product->name }}</span>
                            <span class="item-meta">{{ $product->type }}</span>
                            <span class="item-price">{{ $startPrice > 0 ? 'Rp '.number_format($startPrice, 0, ',', '.') : 'Harga dinamis' }}</span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="promo-grid">
            <a class="promo-tile p1" href="{{ route('public.promo') }}"><strong>Cashback 10%</strong><small>Promo Mingguan Game</small></a>
            <a class="promo-tile p2" href="{{ route('public.promo') }}"><strong>Bundle Hemat</strong><small>Top Up + Voucher</small></a>
            <a class="promo-tile p3" href="{{ route('public.promo') }}"><strong>Diskon PPOB</strong><small>Pulsa, Token, Paket Data</small></a>
            <a class="promo-tile p4" href="{{ route('public.promo') }}"><strong>Event Acara</strong><small>Hadiah untuk transaksi rutin</small></a>
        </section>

        <section class="quick-checkout" id="quick-checkout">
            <div class="checkout-box">
                <h3>Panduan FAQ Lengkap</h3>
                <div class="faq-wrap" style="margin-top:0;">
                    @foreach ($faqItems as $faq)
                        <details>
                            <summary>{{ $faq['q'] }}</summary>
                            <p>{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>

            <div class="checkout-box">
                <h3>Checkout Cepat</h3>
                <form method="post" action="{{ route('storefront.checkout') }}">
                    @csrf
                    <div class="checkout-grid">
                        <div style="grid-column:1/-1;">
                            <label for="product_id">Pilih Produk</label>
                            <select id="product_id" name="product_id" required>
                                <option value="">Pilih produk</option>
                                @foreach ($productsByCategory as $category => $products)
                                    <optgroup label="{{ $category }}">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ (float) ($startingPrices[$product->id] ?? 0) }}" @selected((string) old('product_id') === (string) $product->id)>
                                                {{ $product->name }} ({{ $product->type }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="customer_target">User ID / Target</label>
                            <input id="customer_target" name="customer_target" type="text" value="{{ old('customer_target') }}" placeholder="User ID / Phone / Meter Number">
                        </div>

                        <div>
                            <label for="quantity">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="10" value="{{ old('quantity', 1) }}">
                        </div>

                        <div>
                            <label for="gateway">Gateway</label>
                            <select id="gateway" name="gateway" required>
                                @foreach ($gateways as $gateway)
                                    <option value="{{ $gateway }}" @selected(old('gateway') === $gateway)>{{ $gateway }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="method">Metode (opsional)</label>
                            <input id="method" name="method" type="text" value="{{ old('method') }}" placeholder="VA / QRIS / E-Wallet">
                        </div>

                        @if (session('checkout_challenge_question'))
                            <div style="grid-column:1/-1;">
                                <label for="security_challenge_answer">Verifikasi Keamanan</label>
                                <input id="security_challenge_answer" name="security_challenge_answer" type="text" value="{{ old('security_challenge_answer') }}" placeholder="{{ session('checkout_challenge_question') }}">
                            </div>
                        @endif

                        <div class="estimate" style="grid-column:1/-1;">
                            <div class="estimate-row"><span>Produk</span><strong id="estimate-product">-</strong></div>
                            <div class="estimate-row"><span>Quantity</span><strong id="estimate-qty">1</strong></div>
                            <div class="estimate-row"><span>Gateway</span><strong id="estimate-gateway">MIDTRANS</strong></div>
                            <div class="estimate-row estimate-total"><span>Estimasi Total</span><strong id="estimate-total">Dihitung otomatis</strong></div>
                        </div>
                    </div>

                    <div class="checkout-submit">
                        <button type="submit">Buat Order + Payment</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="support-wrap">
            <h3 style="margin:0 0 10px; color:#fff;">Dukungan Pelanggan</h3>
            <div class="support-grid">
                <div class="support-item">Messenger</div>
                <div class="support-item">WhatsApp</div>
                <div class="support-item">Email</div>
                <div class="support-item">FAQ</div>
                <div class="support-item">Laporan Balik</div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const heroTrack = document.getElementById('hero-track');
            const heroDots = document.querySelectorAll('[data-hero-slide]');
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const gatewaySelect = document.getElementById('gateway');
            const estimateProduct = document.getElementById('estimate-product');
            const estimateQty = document.getElementById('estimate-qty');
            const estimateGateway = document.getElementById('estimate-gateway');
            const estimateTotal = document.getElementById('estimate-total');
            const productButtons = document.querySelectorAll('[data-product-id]');

            function formatRupiah(value) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(value);
            }

            function updateEstimate() {
                if (!productSelect || !estimateProduct || !estimateQty || !estimateGateway || !estimateTotal) {
                    return;
                }

                const option = productSelect.options[productSelect.selectedIndex] || null;
                const label = option ? String(option.text || '-').trim() : '-';
                const qty = Math.max(1, parseInt(String(quantityInput?.value || '1'), 10) || 1);
                const gateway = String(gatewaySelect?.value || '-');
                const unitPrice = option ? parseFloat(String(option.getAttribute('data-price') || '0')) : 0;

                estimateProduct.textContent = label;
                estimateQty.textContent = String(qty);
                estimateGateway.textContent = gateway;
                estimateTotal.textContent = unitPrice > 0 ? formatRupiah(unitPrice * qty) : 'Dihitung otomatis';
            }

            function showHero(index) {
                if (!heroTrack) {
                    return;
                }

                heroTrack.style.transform = 'translateX(-' + (index * 100) + '%)';
                heroDots.forEach(function (dot, i) {
                    dot.classList.toggle('is-active', i === index);
                });
            }

            if (productSelect) {
                productSelect.addEventListener('change', updateEstimate);
            }

            if (quantityInput) {
                quantityInput.addEventListener('input', updateEstimate);
            }

            if (gatewaySelect) {
                gatewaySelect.addEventListener('change', updateEstimate);
            }

            productButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (!productSelect) {
                        return;
                    }

                    productSelect.value = String(button.getAttribute('data-product-id') || '');
                    productSelect.dispatchEvent(new Event('change'));
                    document.getElementById('quick-checkout')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            let heroIndex = 0;
            heroDots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    heroIndex = parseInt(String(dot.getAttribute('data-hero-slide') || '0'), 10) || 0;
                    showHero(heroIndex);
                });
            });

            if (heroDots.length > 1) {
                setInterval(function () {
                    heroIndex = (heroIndex + 1) % heroDots.length;
                    showHero(heroIndex);
                }, 5000);
            }

            showHero(0);
            updateEstimate();
        });
    </script>
</x-layouts.app>