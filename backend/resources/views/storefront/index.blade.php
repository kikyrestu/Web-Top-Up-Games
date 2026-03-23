<x-layouts.app :title="'TopUp Atlas - Instant Top Up'">
    @php
        $allProducts = $productsByCategory->flatten(1)->values();
        $popularProducts = $allProducts->take(10);
        $sectionProducts = $productsByCategory->take(6);
        $promoItems = [
            ['title' => 'Ramadan Mega Deal', 'desc' => 'Cashback hingga 10%', 'class' => 'promo-1'],
            ['title' => 'Bundle Hemat', 'desc' => 'Diskon top up pilihan', 'class' => 'promo-2'],
            ['title' => 'Mystic Event', 'desc' => 'Promo malam ini', 'class' => 'promo-3'],
            ['title' => 'Special Week', 'desc' => 'Hadiah transaksi rutin', 'class' => 'promo-4'],
            ['title' => 'Voucher Blast', 'desc' => 'Voucher digital all-in', 'class' => 'promo-5'],
            ['title' => 'Top Up Sprint', 'desc' => 'Jalur tercepat hari ini', 'class' => 'promo-6'],
        ];
    @endphp

    <style>
        :root {
            --fs-hero: clamp(28px, 3.1vw, 38px);
            --fs-section: clamp(22px, 2.2vw, 30px);
            --fs-subsection: clamp(17px, 1.6vw, 21px);
            --fs-body: 14px;
            --fs-meta: 12px;
            --space-section: 18px;
            --space-box: 18px;
            --radius-lg: 12px;
            --radius-md: 10px;
            --border-strong: 1px;
        }

        .uni-wrap {
            display: grid;
            gap: var(--space-section);
            color: #eaf1ff;
            max-width: 1440px;
            margin: 0 auto;
        }

        .hero-banner {
            border: var(--border-strong) solid #28446f;
            border-radius: var(--radius-lg);
            overflow: hidden;
            position: relative;
            background: #0f1e39;
        }

        .hero-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #5f7eaf;
            background: #0f203f;
            color: #e9f1ff;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            z-index: 3;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .hero-prev { left: 10px; }
        .hero-next { right: 10px; }

        .hero-track {
            display: flex;
            transition: transform .5s ease;
        }

        .hero-slide {
            min-width: 100%;
            min-height: 300px;
            padding: 28px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 10px;
        }

        .hero-slide h1 {
            margin: 0;
            font-size: var(--fs-hero);
            color: #fff;
            line-height: 1.05;
            letter-spacing: -0.015em;
        }

        .hero-slide p {
            margin: 0;
            font-size: 15px;
            color: #d2e2ff;
            line-height: 1.45;
        }

        .hero-slide a {
            display: inline-flex;
            width: fit-content;
            border-radius: 9px;
            padding: 8px 12px;
            background: #3f6ff0;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
        }

        .h1 { background: linear-gradient(130deg, #0d1630, #0f2b5a 55%, #3553bf); }
        .h2 { background: linear-gradient(130deg, #0f1e3f, #1f3f8c 55%, #3f6fcf); }
        .h3 { background: linear-gradient(130deg, #101a37, #203976 55%, #355eb8); }

        .hero-dots {
            position: absolute;
            right: 10px;
            bottom: 10px;
            display: flex;
            gap: 7px;
        }

        .hero-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            border: 1px solid #ffffff85;
            background: #ffffff4f;
            cursor: pointer;
        }

        .hero-dot.is-active {
            background: #5f8bff;
            border-color: #5f8bff;
        }

        .section-box {
            border: var(--border-strong) solid #274069;
            border-radius: var(--radius-lg);
            background: #132544;
            padding: var(--space-box);
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }

        .section-head h2,
        .section-head h3 {
            margin: 0;
            color: #fff;
            font-size: var(--fs-section);
            letter-spacing: -0.01em;
        }

        .section-head h3 {
            font-size: var(--fs-subsection);
        }

        .section-head a {
            border: 1px solid #3f6296;
            color: #dbe8ff;
            border-radius: 999px;
            text-decoration: none;
            font-size: var(--fs-meta);
            font-weight: 800;
            padding: 6px 10px;
        }

        .popular-rail,
        .category-rail {
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            gap: 12px;
        }

        .card-item {
            border: var(--border-strong) solid #33517f;
            border-radius: var(--radius-md);
            background: #0e1e38;
            padding: 8px;
            display: grid;
            gap: 8px;
            text-align: left;
            cursor: pointer;
            color: #eef4ff;
        }

        .card-item:hover {
            border-color: #5f83be;
        }

        .card-thumb {
            aspect-ratio: 3 / 4;
            border-radius: calc(var(--radius-md) - 2px);
            border: var(--border-strong) solid #2c456e;
            background: linear-gradient(130deg, #27426b, #3e659f);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
        }

        .card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-name {
            font-size: var(--fs-meta);
            font-weight: 800;
            min-height: 24px;
            line-height: 1.3;
        }

        .card-price {
            font-size: var(--fs-meta);
            color: #9bc1ff;
            font-weight: 800;
        }

        .mini-button {
            border: var(--border-strong) solid #3f6296;
            border-radius: 7px;
            padding: 4px 0;
            text-align: center;
            font-size: var(--fs-meta);
            font-weight: 800;
            color: #dbe8ff;
            background: #102543;
        }

        .section-foot {
            margin-top: 12px;
            display: flex;
            justify-content: center;
        }

        .section-foot a {
            border: var(--border-strong) solid #3f6296;
            border-radius: 999px;
            padding: 6px 12px;
            text-decoration: none;
            color: #dbe8ff;
            font-size: 12px;
            font-weight: 800;
            background: #102543;
        }

        .benefit-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .benefit {
            border: var(--border-strong) solid #2e4a75;
            border-radius: var(--radius-md);
            background: #182c4d;
            padding: 14px;
        }

        .benefit strong {
            display: block;
            color: #fff;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .benefit span {
            color: #a9bfdf;
            font-size: var(--fs-body);
            line-height: 1.45;
        }

        .promo-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .promo-card {
            min-height: 125px;
            border-radius: var(--radius-md);
            border: var(--border-strong) solid #3a5b8d;
            padding: 12px;
            display: grid;
            align-content: end;
            gap: 6px;
            color: #fff;
        }

        .promo-card strong { font-size: 15px; }
        .promo-card span { font-size: var(--fs-body); color: #d9e8ff; }
        .promo-1 { background: linear-gradient(130deg, #172d73, #3056c1); }
        .promo-2 { background: linear-gradient(130deg, #193066, #335dbe); }
        .promo-3 { background: linear-gradient(130deg, #162a62, #2f54ad); }
        .promo-4 { background: linear-gradient(130deg, #1d356d, #3a67ca); }
        .promo-5 { background: linear-gradient(130deg, #1a325f, #3561b4); }
        .promo-6 { background: linear-gradient(130deg, #182b57, #2e54a3); }

        .checkout-area {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .faq-box,
        .checkout-box,
        .support-box,
        .footer-box {
            border: var(--border-strong) solid #274069;
            border-radius: var(--radius-lg);
            background: #132544;
            padding: var(--space-box);
        }

        .faq-box details {
            border-top: 1px solid #2f4a75;
            padding: 9px 0;
        }

        .faq-box details:first-of-type { border-top: none; }

        .faq-box summary {
            cursor: pointer;
            font-size: 14px;
            font-weight: 800;
            color: #f2f7ff;
            line-height: 1.45;
        }

        .faq-box p {
            margin: 7px 0 0;
            color: #aac1e2;
            font-size: var(--fs-body);
            line-height: 1.45;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .checkout-grid label {
            display: block;
            margin-bottom: 4px;
            font-size: var(--fs-body);
            color: #c3d4ee;
            font-weight: 700;
        }

        .estimate {
            border: var(--border-strong) solid #2e4a75;
            border-radius: var(--radius-md);
            background: #10213c;
            padding: 10px;
            display: grid;
            gap: 6px;
        }

        .estimate-row {
            display: flex;
            justify-content: space-between;
            font-size: var(--fs-body);
            color: #d7e4fb;
            gap: 8px;
        }

        .estimate-row.total {
            border-top: 1px dashed #37547f;
            padding-top: 7px;
            color: #9bc1ff;
            font-weight: 800;
        }

        .checkout-submit {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .checkout-submit button {
            border: none;
            border-radius: 10px;
            padding: 10px 13px;
            background: linear-gradient(120deg, #3f6ff0, #2b57cc);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .support-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
        }

        .subscribe-box {
            border: var(--border-strong) solid #274069;
            border-radius: var(--radius-lg);
            background: #132544;
            padding: var(--space-box);
            display: grid;
            gap: 12px;
        }

        .subscribe-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .subscribe-head h3 {
            margin: 0;
            font-size: var(--fs-section);
            color: #fff;
        }

        .subscribe-head p {
            margin: 0;
            color: #a5bbdd;
            font-size: var(--fs-body);
        }

        .subscribe-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .subscribe-input {
            border: var(--border-strong) solid #2f4a74;
            border-radius: var(--radius-md);
            background: #0f1f39;
            color: #e8f0ff;
            padding: 10px 12px;
            font-size: 13px;
            width: 100%;
        }

        .subscribe-btn {
            border: none;
            border-radius: var(--radius-md);
            padding: 10px 14px;
            background: linear-gradient(120deg, #3f6ff0, #2b57cc);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
        }

        .social-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .social-chip {
            border: var(--border-strong) solid #35547f;
            border-radius: 999px;
            padding: 6px 11px;
            font-size: var(--fs-body);
            font-weight: 800;
            color: #cfe0ff;
            background: #102543;
        }

        .support-item {
            border: var(--border-strong) solid #32507d;
            border-radius: var(--radius-md);
            background: #0f1f39;
            padding: 12px;
            text-align: center;
            color: #dbe8ff;
            font-size: var(--fs-body);
            font-weight: 800;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-top: 8px;
        }

        .footer-grid h4 {
            margin: 0 0 8px;
            color: #fff;
            font-size: 14px;
        }

        .footer-grid p,
        .footer-grid li {
            margin: 0;
            color: #9fb7d9;
            font-size: var(--fs-body);
            line-height: 1.5;
        }

        .footer-grid ul {
            margin: 0;
            padding-left: 16px;
        }

        .legal {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #2f4a75;
            color: #8fa8cc;
            font-size: 12px;
        }

        @media (max-width: 1080px) {
            :root {
                --fs-hero: clamp(24px, 3.4vw, 32px);
                --fs-section: clamp(20px, 2.2vw, 26px);
                --fs-subsection: clamp(16px, 1.8vw, 19px);
            }

            .popular-rail,
            .category-rail {
                grid-template-columns: repeat(6, minmax(0, 1fr));
            }

            .promo-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            :root {
                --fs-hero: 26px;
                --fs-section: 22px;
                --fs-subsection: 18px;
            }

            .hero-slide {
                min-height: 210px;
                padding: 18px;
            }

            .hero-nav {
                display: none;
            }

            .hero-slide h1 {
                line-height: 1.1;
            }

            .popular-rail,
            .category-rail {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .benefit-row {
                grid-template-columns: 1fr;
            }

            .checkout-area {
                grid-template-columns: 1fr;
            }

            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .support-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .subscribe-row {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    <div class="uni-wrap">
        <section class="hero-banner">
            <div class="hero-track" id="hero-track">
                <article class="hero-slide h1">
                    <h1>Ramadan Taktis 2026: Top Up Anti Ribet</h1>
                    <p>Pengalaman top up cepat dengan tampilan marketplace gelap seperti platform favorit kamu.</p>
                    <a href="#quick-checkout">Baca Selengkapnya</a>
                </article>
                <article class="hero-slide h2">
                    <h1>Game Populer dan PPOB Lengkap</h1>
                    <p>Semua kategori digital ada dalam satu alur transaksi yang sederhana.</p>
                    <a href="#catalog">Lihat Kategori</a>
                </article>
                <article class="hero-slide h3">
                    <h1>Promo Dan Acara Mingguan</h1>
                    <p>Voucher cashback dan bonus event untuk top up harianmu.</p>
                    <a href="{{ route('public.promo') }}">Lihat Promo</a>
                </article>
            </div>
            <div class="hero-dots">
                <button class="hero-dot is-active" type="button" data-slide="0"></button>
                <button class="hero-dot" type="button" data-slide="1"></button>
                <button class="hero-dot" type="button" data-slide="2"></button>
            </div>
            <button class="hero-nav hero-prev" type="button" aria-label="Slide sebelumnya" id="hero-prev">‹</button>
            <button class="hero-nav hero-next" type="button" aria-label="Slide berikutnya" id="hero-next">›</button>
        </section>

        <section class="section-box" id="catalog">
            <div class="section-head">
                <h2>Game Populer</h2>
            </div>
            <div class="popular-rail">
                @foreach ($popularProducts as $product)
                    @php
                        $thumbRaw = is_array($product->meta ?? null) ? ((string) (($product->meta['thumbnail'] ?? $product->meta['icon'] ?? '') ?: '')) : '';
                        $thumbIsImage = $thumbRaw !== '' && (str_starts_with($thumbRaw, 'http://') || str_starts_with($thumbRaw, 'https://') || str_starts_with($thumbRaw, '/'));
                        $thumb = $thumbRaw !== '' ? $thumbRaw : strtoupper(substr((string) $product->name, 0, 1));
                        $startPrice = (float) ($startingPrices[$product->id] ?? 0);
                    @endphp
                    <button class="card-item" type="button" data-product-id="{{ (int) $product->id }}">
                        <span class="card-thumb">
                            @if ($thumbIsImage)
                                <img src="{{ $thumb }}" alt="{{ $product->name }}">
                            @else
                                {{ $thumb }}
                            @endif
                        </span>
                        <span class="card-name">{{ $product->name }}</span>
                        <span class="card-price">{{ $startPrice > 0 ? 'Rp '.number_format($startPrice, 0, ',', '.') : 'Harga dinamis' }}</span>
                        <span class="mini-button">Top Up</span>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="benefit-row">
            <article class="benefit"><strong>Isi ulang instan</strong><span>Akses cepat untuk semua game dan produk digital.</span></article>
            <article class="benefit"><strong>Dapatkan hadiah besar</strong><span>Promo cashback dan bonus untuk pengguna aktif.</span></article>
            <article class="benefit"><strong>Terpercaya</strong><span>Jalur pembayaran aman dengan monitoring transaksi.</span></article>
        </section>

        @foreach ($sectionProducts as $categoryName => $products)
            <section class="section-box">
                <div class="section-head">
                    <h3>{{ $categoryName }}</h3>
                    <a href="#quick-checkout">Lainnya</a>
                </div>
                <div class="category-rail">
                    @foreach ($products->take(7) as $product)
                        @php
                            $thumbRaw = is_array($product->meta ?? null) ? ((string) (($product->meta['thumbnail'] ?? $product->meta['icon'] ?? '') ?: '')) : '';
                            $thumbIsImage = $thumbRaw !== '' && (str_starts_with($thumbRaw, 'http://') || str_starts_with($thumbRaw, 'https://') || str_starts_with($thumbRaw, '/'));
                            $thumb = $thumbRaw !== '' ? $thumbRaw : strtoupper(substr((string) $product->name, 0, 1));
                            $startPrice = (float) ($startingPrices[$product->id] ?? 0);
                        @endphp
                        <button class="card-item" type="button" data-product-id="{{ (int) $product->id }}">
                            <span class="card-thumb">
                                @if ($thumbIsImage)
                                    <img src="{{ $thumb }}" alt="{{ $product->name }}">
                                @else
                                    {{ $thumb }}
                                @endif
                            </span>
                            <span class="card-name">{{ $product->name }}</span>
                            <span class="card-price">{{ $startPrice > 0 ? 'Rp '.number_format($startPrice, 0, ',', '.') : 'Harga dinamis' }}</span>
                            <span class="mini-button">Top Up</span>
                        </button>
                    @endforeach
                </div>
                <div class="section-foot">
                    <a href="#quick-checkout">Lihat Produk</a>
                </div>
            </section>
        @endforeach

        <section class="section-box">
            <div class="section-head"><h3>Promo dan Acara</h3><a href="{{ route('public.promo') }}">Lainnya</a></div>
            <div class="promo-grid">
                @foreach ($promoItems as $promo)
                    <article class="promo-card {{ $promo['class'] }}">
                        <strong>{{ $promo['title'] }}</strong>
                        <span>{{ $promo['desc'] }}</span>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="checkout-area" id="quick-checkout">
            <div class="faq-box">
                <div class="section-head"><h3>Panduan FAQ Lengkap</h3></div>
                <details>
                    <summary>Voucher di Indonesia berlaku untuk apa?</summary>
                    <p>Voucher bisa dipakai untuk top up item dan layanan digital pada produk aktif.</p>
                </details>
                <details>
                    <summary>Tidak bisa menemukan metode bayar?</summary>
                    <p>Pilih gateway default dulu lalu isi preferensi metode pada form checkout.</p>
                </details>
                <details>
                    <summary>Saldo terpotong tapi status belum selesai?</summary>
                    <p>Gunakan Cek Transaksi dan kirim kode order ke dukungan pelanggan.</p>
                </details>
                <details>
                    <summary>Bagaimana melakukan refund?</summary>
                    <p>Refund mengikuti status provider dan gateway, tim akan verifikasi manual.</p>
                </details>
            </div>

            <div class="checkout-box">
                <div class="section-head"><h3>Checkout Cepat</h3></div>
                <form method="post" action="{{ route('storefront.checkout') }}">
                    @csrf
                    <div class="checkout-grid">
                        <div style="grid-column:1/-1;">
                            <label for="product_id">Produk</label>
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
                            <label for="customer_target">Target</label>
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
                            <div class="estimate-row total"><span>Estimasi Total</span><strong id="estimate-total">Dihitung otomatis</strong></div>
                        </div>
                    </div>

                    <div class="checkout-submit">
                        <button type="submit">Buat Order + Payment</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="support-box">
            <div class="section-head"><h3>Dukungan Pelanggan</h3></div>
            <div class="support-grid">
                <div class="support-item">Messenger</div>
                <div class="support-item">WhatsApp</div>
                <div class="support-item">Email</div>
                <div class="support-item">FAQ</div>
                <div class="support-item">Laporan Balik</div>
            </div>
        </section>

        <section class="subscribe-box">
            <div class="subscribe-head">
                <h3>Berlangganan</h3>
                <p>Dapatkan promo terbaru langsung ke email kamu.</p>
            </div>
            <div class="subscribe-row">
                <input class="subscribe-input" type="email" placeholder="Masukkan email aktif...">
                <button class="subscribe-btn" type="button">Langganan Sekarang</button>
            </div>
            <div class="social-row">
                <span class="social-chip">Facebook</span>
                <span class="social-chip">Instagram</span>
                <span class="social-chip">YouTube</span>
                <span class="social-chip">X / Twitter</span>
            </div>
        </section>

        <section class="footer-box">
            <div class="footer-grid">
                <div>
                    <h4>TopUp Atlas</h4>
                    <p>Marketplace top up game dan PPOB dengan jalur pembayaran aman dan cepat.</p>
                </div>
                <div>
                    <h4>Produk dan Layanan</h4>
                    <ul>
                        <li>Game</li>
                        <li>Voucher</li>
                        <li>PPOB</li>
                        <li>Promo</li>
                    </ul>
                </div>
                <div>
                    <h4>Informasi</h4>
                    <ul>
                        <li>FAQ</li>
                        <li>Panduan</li>
                        <li>Syarat dan Ketentuan</li>
                        <li>Kebijakan Privasi</li>
                    </ul>
                </div>
                <div>
                    <h4>Korporat dan Kemitraan</h4>
                    <ul>
                        <li>Tentang Kami</li>
                        <li>Program Kemitraan</li>
                        <li>Karier</li>
                        <li>Kontak Bisnis</li>
                    </ul>
                </div>
            </div>
            <div class="legal">© 2026 TopUp Atlas. All Rights Reserved.</div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const heroTrack = document.getElementById('hero-track');
            const heroDots = document.querySelectorAll('[data-slide]');
            const productSelect = document.getElementById('product_id');
            const quantityInput = document.getElementById('quantity');
            const gatewaySelect = document.getElementById('gateway');
            const estimateProduct = document.getElementById('estimate-product');
            const estimateQty = document.getElementById('estimate-qty');
            const estimateGateway = document.getElementById('estimate-gateway');
            const estimateTotal = document.getElementById('estimate-total');
            const cards = document.querySelectorAll('[data-product-id]');
            const heroPrev = document.getElementById('hero-prev');
            const heroNext = document.getElementById('hero-next');

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
                const productLabel = option ? String(option.text || '-').trim() : '-';
                const qty = Math.max(1, parseInt(String(quantityInput?.value || '1'), 10) || 1);
                const gateway = String(gatewaySelect?.value || '-');
                const unitPrice = option ? parseFloat(String(option.getAttribute('data-price') || '0')) : 0;

                estimateProduct.textContent = productLabel;
                estimateQty.textContent = String(qty);
                estimateGateway.textContent = gateway;
                estimateTotal.textContent = unitPrice > 0 ? formatRupiah(unitPrice * qty) : 'Dihitung otomatis';
            }

            function showSlide(index) {
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

            cards.forEach(function (card) {
                card.addEventListener('click', function () {
                    if (!productSelect) {
                        return;
                    }

                    productSelect.value = String(card.getAttribute('data-product-id') || '');
                    productSelect.dispatchEvent(new Event('change'));
                    document.getElementById('quick-checkout')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            let currentSlide = 0;
            heroDots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    currentSlide = parseInt(String(dot.getAttribute('data-slide') || '0'), 10) || 0;
                    showSlide(currentSlide);
                });
            });

            if (heroPrev) {
                heroPrev.addEventListener('click', function () {
                    currentSlide = (currentSlide - 1 + heroDots.length) % heroDots.length;
                    showSlide(currentSlide);
                });
            }

            if (heroNext) {
                heroNext.addEventListener('click', function () {
                    currentSlide = (currentSlide + 1) % heroDots.length;
                    showSlide(currentSlide);
                });
            }

            if (heroDots.length > 1) {
                setInterval(function () {
                    currentSlide = (currentSlide + 1) % heroDots.length;
                    showSlide(currentSlide);
                }, 5000);
            }

            showSlide(0);
            updateEstimate();
        });
    </script>
</x-layouts.app>