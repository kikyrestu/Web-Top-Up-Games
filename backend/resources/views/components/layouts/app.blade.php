<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $seoMetaTitle = $seo['meta_title'] ?? null;
        $seoMetaDescription = $seo['meta_description'] ?? null;
        $seoMetaKeywords = $seo['meta_keywords'] ?? null;
        $seoOgTitle = $seo['og_title'] ?? null;
        $seoOgDescription = $seo['og_description'] ?? null;
        $seoOgImagePath = $seo['og_image_path'] ?? null;
        $finalTitle = $seoMetaTitle ?: ($title ?? 'Web Top-Up Games');
    @endphp
    <title>{{ $finalTitle }}</title>
    @if (!empty($seoMetaDescription))
        <meta name="description" content="{{ $seoMetaDescription }}">
    @endif
    @if (!empty($seoMetaKeywords))
        <meta name="keywords" content="{{ $seoMetaKeywords }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seoOgTitle ?: $finalTitle }}">
    @if (!empty($seoOgDescription) || !empty($seoMetaDescription))
        <meta property="og:description" content="{{ $seoOgDescription ?: $seoMetaDescription }}">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    @if (!empty($seoOgImagePath))
        <meta property="og:image" content="{{ str_starts_with((string) $seoOgImagePath, 'http') ? $seoOgImagePath : url((string) $seoOgImagePath) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #08152d;
            --ink: #e8f0ff;
            --ink-soft: #9ab0d1;
            --line: #2b4368;
            --panel: #122445;
            --accent: #4f7dff;
            --accent-2: #8ab0ff;
            --danger: #b00020;
            --ok: #6e9dff;
            --warn: #6e9dff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: linear-gradient(180deg, #061127 0%, #081a34 45%, #07162e 100%);
            color: var(--ink);
            font-family: 'Manrope', sans-serif;
        }

        .shell {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0 0 56px;
        }

        .content-pad {
            padding: 0 clamp(20px, 2.2vw, 32px);
            max-width: 1440px;
            margin: 0 auto;
        }

        .topbar {
            display: grid;
            gap: 10px;
            margin-bottom: 10px;
            background: #0a1731;
            border-top: 1px solid #1f365a;
            border-bottom: 1px solid #1f365a;
            border-left: 0;
            border-right: 0;
            border-radius: 0;
            padding: 12px clamp(20px, 2.2vw, 32px);
        }

        .topbar-frame {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
        }

        .top-utility {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #223a5f;
            padding-bottom: 8px;
            color: #8ea8cf;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .top-utility .left,
        .top-utility .right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .utility-link {
            color: #8ea8cf;
            text-decoration: none;
        }

        .utility-link:hover {
            color: #dce8ff;
        }

        .topbar-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 4px 2px 2px;
        }

        .brand-wrap {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .brand {
            text-decoration: none;
            color: #ffffff;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 30px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-sub {
            color: #8ea8cf;
            font-size: 12px;
            font-weight: 700;
        }

        .topbar-center {
            flex: 1;
            max-width: 520px;
            margin: 0 10px;
        }

        .top-search {
            width: 100%;
            border: 1px solid #2f4a74;
            border-radius: 10px;
            background: #0a1830;
            color: #dce8ff;
            padding: 10px 12px;
            font-size: 13px;
        }

        .top-search::placeholder {
            color: #9fb5cd;
        }

        .quick-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .action-link {
            border: 1px solid #2f4a74;
            border-radius: 8px;
            padding: 9px 12px;
            text-decoration: none;
            color: #cfe0ff;
            font-weight: 800;
            font-size: 13px;
            background: #102543;
        }

        .action-link:hover {
            border-color: #4f73ab;
            background: #17325a;
        }

        .cta-link {
            border-color: transparent;
            background: linear-gradient(120deg, #3f6ff0, #2b57cc);
            color: #fff;
        }

        .locale-pill {
            border: 1px solid #2f4a74;
            border-radius: 999px;
            padding: 6px 10px;
            color: #cfe0ff;
            font-size: 12px;
            font-weight: 800;
            background: #102543;
            text-decoration: none;
        }

        .market-strip {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            border-top: 1px solid #223a5f;
            padding-top: 8px;
        }

        .market-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #2f4a74;
            border-radius: 8px;
            padding: 6px 9px;
            color: #cfe0ff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            background: #102543;
        }

        .market-link:hover {
            border-color: #89acd3;
            color: #ffffff;
        }

        .market-icon {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1a355d;
            color: #ffffff;
        }

        .market-icon svg {
            width: 13px;
            height: 13px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .admin-strip {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            border-top: 1px dashed #30507b;
            padding-top: 10px;
        }

        .admin-link {
            border: 1px solid #2f4a74;
            border-radius: 999px;
            padding: 7px 11px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            color: #cfe0ff;
            background: #102543;
        }

        .admin-shell {
            display: grid;
            grid-template-columns: 240px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
        }

        .admin-sidebar {
            position: sticky;
            top: 14px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #102543;
            padding: 12px;
            display: grid;
            gap: 8px;
        }

        .admin-sidebar-title {
            margin: 0;
            color: #ffffff;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 16px;
            letter-spacing: -0.01em;
        }

        .admin-sidebar-sub {
            margin: -2px 0 8px;
            color: #8ea8cf;
            font-size: 12px;
            font-weight: 700;
        }

        .admin-nav-link {
            border: 1px solid #2f4a74;
            border-radius: 10px;
            padding: 9px 11px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            color: #cfe0ff;
            background: #0f213f;
        }

        .admin-nav-link:hover {
            border-color: #4f73ab;
            background: #17325a;
        }

        .admin-nav-link.is-active {
            border-color: #5f86d6;
            background: linear-gradient(120deg, #25457f, #1b3764);
            color: #ffffff;
        }

        .admin-main {
            min-width: 0;
        }

        .logout-btn {
            border: 1px solid #2f4a74;
            border-radius: 999px;
            padding: 8px 12px;
            background: #102543;
            color: #dce8ff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            font-family: 'Manrope', sans-serif;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: none;
            padding: 18px;
        }

        .flash {
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
            border: 1px solid;
            font-weight: 700;
        }

        .flash-ok {
            border-color: #2d5f4a;
            background: #123527;
            color: #8ad8ad;
        }

        .flash-err {
            border-color: #7a3441;
            background: #351822;
            color: #f4a5b0;
        }

        h1, h2, h3 {
            font-family: 'Space Grotesk', sans-serif;
            margin: 0 0 10px;
        }

        .muted { color: var(--ink-soft); }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            border-bottom: 1px solid var(--line);
            text-align: left;
            padding: 10px 8px;
            vertical-align: top;
        }

        th {
            font-size: 12px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--ink-soft);
        }

        .tag {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid;
        }

        .tag-pass { background: #edf9f1; border-color: #a8debc; color: var(--ok); }
        .tag-warn { background: #fff8e5; border-color: #f7d889; color: var(--warn); }
        .tag-fail { background: #fff2f4; border-color: #efb8c0; color: var(--danger); }

        .btn {
            border: none;
            border-radius: 12px;
            padding: 11px 16px;
            font-weight: 800;
            font-family: 'Manrope', sans-serif;
            cursor: pointer;
            background: linear-gradient(120deg, #3f6ff0, #2b57cc);
            color: #fff;
        }

        .btn-ghost {
            background: #102543;
            border: 1px solid var(--line);
            color: #dce8ff;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #2f4a74;
            border-radius: 999px;
            padding: 8px 12px;
            text-decoration: none;
            color: #dce8ff;
            font-size: 12px;
            font-weight: 800;
            background: #102543;
            font-family: 'Manrope', sans-serif;
            cursor: pointer;
        }

        .pill:hover {
            border-color: #4f73ab;
            background: #17325a;
        }

        input, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 11px;
            font-size: 14px;
            background: #0d1d38;
            color: #e8f0ff;
        }

        .grid {
            display: grid;
            gap: 14px;
        }

        .cards {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background: #102543;
        }

        .card .k { color: var(--ink-soft); font-size: 12px; text-transform: uppercase; font-weight: 700; }
        .card .v { font-size: 26px; font-family: 'Space Grotesk', sans-serif; font-weight: 700; }

        @media (min-width: 1700px) {
            .content-pad,
            .topbar-frame {
                max-width: 1600px;
            }

            .topbar {
                padding-top: 14px;
                padding-bottom: 14px;
            }

            .topbar-center {
                max-width: 620px;
            }

            .quick-actions {
                gap: 10px;
            }
        }

        @media (min-width: 2200px) {
            .content-pad,
            .topbar-frame {
                max-width: 1760px;
            }
        }

        @media (max-width: 900px) {
            .cards { grid-template-columns: 1fr 1fr; }
            .admin-shell {
                grid-template-columns: 1fr;
            }
            .admin-sidebar {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .cards { grid-template-columns: 1fr; }
            .shell { padding: 0 0 36px; }
            .top-utility {
                flex-direction: column;
                align-items: flex-start;
            }
            .topbar-main {
                align-items: flex-start;
                flex-direction: column;
            }
            .topbar-center {
                width: 100%;
                max-width: none;
                margin: 0;
            }
            .quick-actions { justify-content: flex-start; }
            .topbar { padding: 10px 12px; }
            .content-pad { padding: 0 12px; }
        }
    </style>
</head>
<body>
<div class="shell">
    @php
        $isAdminUser = auth()->check() && strtolower((string) (auth()->user()->role ?? '')) === 'admin';
        $isAdminRoute = request()->routeIs('admin.*');
    @endphp
    <div class="topbar">
        <div class="topbar-frame">
            <div class="top-utility">
                <div class="left">
                    <a class="utility-link" href="{{ route('storefront.index') }}">Instant Top Up</a>
                    <span>|</span>
                    <a class="utility-link" href="{{ route('public.promo') }}">Promo dan Acara</a>
                    <span>|</span>
                    <a class="utility-link" href="{{ route('public.reviews.index') }}">Keanggotaan</a>
                    <span>|</span>
                    <a class="utility-link" href="{{ route('public.articles.index') }}">Lainnya</a>
                </div>
                <div class="right">
                    <a class="locale-pill" href="#">Indonesia</a>
                    <a class="locale-pill" href="#">IDR</a>
                </div>
            </div>

            <div class="topbar-main">
                <div class="brand-wrap">
                    <a class="brand" href="{{ route('storefront.index') }}">TopUp Atlas</a>
                    <span class="brand-sub">Top up game and PPOB platform</span>
                </div>

                <div class="topbar-center">
                    <input class="top-search" type="text" placeholder="Cari game, voucher, atau layanan...">
                </div>

                <div class="quick-actions">
                    <a class="action-link" href="{{ route('public.check-transaction') }}">Cek Transaksi</a>
                    <a class="action-link" href="{{ route('public.promo') }}">Promo</a>
                    <a class="action-link" href="{{ route('storefront.history') }}">Riwayat</a>
                    @auth
                        <a class="action-link" href="{{ route('account.dashboard') }}">Akun Saya</a>
                    @else
                        <a class="action-link cta-link" href="{{ route('account.login-otp') }}">Masuk</a>
                    @endauth

                    @auth
                        <form method="post" action="{{ route('account.logout') }}">
                            @csrf
                            <button class="logout-btn" type="submit">Logout</button>
                        </form>
                    @endauth
                </div>
            </div>

            <div class="market-strip">
                <a class="market-link" href="{{ route('public.topup.index') }}"><span class="market-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8l8-4 8 4-8 4-8-4z"></path><path d="M6 10v6l6 3 6-3v-6"></path></svg></span> Game</a>
                <a class="market-link" href="{{ route('public.ppob.index') }}"><span class="market-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><path d="M4 10h16"></path></svg></span> PPOB</a>
                <a class="market-link" href="{{ route('public.articles.index') }}"><span class="market-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 5h14v14H5z"></path><path d="M8 9h8M8 13h8M8 17h5"></path></svg></span> Artikel</a>
                <a class="market-link" href="{{ route('public.reviews.index') }}"><span class="market-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.9 5.9L21 9.8l-4.5 4.4 1.1 6.2L12 17.7 6.4 20.4l1.1-6.2L3 9.8l6.1-.9z"></path></svg></span> Ulasan</a>
            </div>

            @if ($isAdminUser && !$isAdminRoute)
                <div class="admin-strip">
                    <a class="admin-link" href="{{ route('admin.dashboard') }}">Admin</a>
                </div>
            @endif
        </div>
    </div>

    <div class="content-pad">
        @if (session('checkout_summary'))
            <div class="flash flash-ok">
                Checkout berhasil diproses. Order sudah dibuat dan invoice payment sudah diinisiasi.
            </div>
        @endif

        @if (session('notice'))
            <div class="flash flash-ok">
                {{ session('notice') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flash flash-err">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($isAdminUser && $isAdminRoute)
            <div class="admin-shell">
                <aside class="admin-sidebar" aria-label="Menu Admin">
                    <h2 class="admin-sidebar-title">Admin Panel</h2>
                    <p class="admin-sidebar-sub">Navigasi cepat operasional</p>
                    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.dashboard.alerts') ? 'is-active' : '' }}" href="{{ route('admin.dashboard.alerts') }}">Alerts</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.catalog.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.catalog.categories.index') }}">Catalog Categories</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.catalog.products.*') ? 'is-active' : '' }}" href="{{ route('admin.catalog.products.index') }}">Catalog Products</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.catalog.providers.*') ? 'is-active' : '' }}" href="{{ route('admin.catalog.providers.index') }}">Catalog Providers</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.nominal.mappings.*') ? 'is-active' : '' }}" href="{{ route('admin.nominal.mappings.index') }}">Nominal Mappings</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.nominal.prices.*') ? 'is-active' : '' }}" href="{{ route('admin.nominal.prices.index') }}">Nominal Prices</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.payment.gateways.*') ? 'is-active' : '' }}" href="{{ route('admin.payment.gateways.index') }}">Payment Gateways</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.pricing.margins.*') ? 'is-active' : '' }}" href="{{ route('admin.pricing.margins.index') }}">Pricing Rules</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.promo.campaigns.*') ? 'is-active' : '' }}" href="{{ route('admin.promo.campaigns.index') }}">Promo Campaigns</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}" href="{{ route('admin.orders.index') }}">Orders</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}" href="{{ route('admin.reviews.index') }}">Reviews</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.security-events.*') ? 'is-active' : '' }}" href="{{ route('admin.security-events.index') }}">Security Events</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.cms.pages.*') ? 'is-active' : '' }}" href="{{ route('admin.cms.pages.index') }}">CMS Pages</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.cms.banners.*') ? 'is-active' : '' }}" href="{{ route('admin.cms.banners.index') }}">CMS Banners</a>
                    <a class="admin-nav-link {{ request()->routeIs('admin.seo.*') ? 'is-active' : '' }}" href="{{ route('admin.seo.index') }}">SEO</a>
                </aside>

                <main class="admin-main">
                    {{ $slot }}
                </main>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
</body>
</html>
