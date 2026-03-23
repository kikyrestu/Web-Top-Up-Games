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
            padding: 0 0 48px;
        }

        .content-pad {
            padding: 0 14px;
        }

        .topbar {
            display: grid;
            gap: 8px;
            margin-bottom: 10px;
            background: #0a1731;
            border-top: 1px solid #1f365a;
            border-bottom: 1px solid #1f365a;
            border-left: 0;
            border-right: 0;
            border-radius: 0;
            padding: 10px 16px;
        }

        .top-utility {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #223a5f;
            padding-bottom: 8px;
            color: #8ea8cf;
            font-size: 10px;
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

        .topbar-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 2px 2px 0;
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
            font-size: 11px;
            font-weight: 700;
        }

        .topbar-center {
            flex: 1;
            max-width: 420px;
            margin: 0 10px;
        }

        .top-search {
            width: 100%;
            border: 1px solid #2f4a74;
            border-radius: 10px;
            background: #0a1830;
            color: #dce8ff;
            padding: 9px 11px;
            font-size: 12px;
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
            padding: 8px 10px;
            text-decoration: none;
            color: #cfe0ff;
            font-weight: 800;
            font-size: 12px;
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
            font-size: 11px;
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
            font-size: 12px;
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
            font-size: 11px;
            font-weight: 800;
            color: #cfe0ff;
            background: #102543;
        }

        .logout-btn {
            border: 1px solid #2f4a74;
            border-radius: 999px;
            padding: 8px 12px;
            background: #102543;
            color: #dce8ff;
            font-size: 12px;
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

        @media (max-width: 900px) {
            .cards { grid-template-columns: 1fr 1fr; }
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
            .content-pad { padding: 0 10px; }
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
        <div class="top-utility">
            <div class="left">
                <span>Instant Top Up</span>
                <span>|</span>
                <span>Pembayaran Aman</span>
                <span>|</span>
                <span>Layanan 24 Jam</span>
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

        @if ($isAdminUser)
            <div class="admin-strip">
                <a class="admin-link" href="{{ route('admin.dashboard') }}">Admin</a>

                @if ($isAdminRoute)
                    <a class="admin-link" href="{{ route('admin.dashboard.alerts') }}">Alerts</a>
                    <a class="admin-link" href="{{ route('admin.cms.pages.index') }}">CMS Pages</a>
                    <a class="admin-link" href="{{ route('admin.cms.banners.index') }}">CMS Banners</a>
                    <a class="admin-link" href="{{ route('admin.seo.index') }}">SEO</a>
                    <a class="admin-link" href="{{ route('admin.audit-logs.index') }}">Audit</a>
                    <a class="admin-link" href="{{ route('admin.security-events.index') }}">Security</a>
                    <a class="admin-link" href="{{ route('admin.orders.index') }}">Orders</a>
                    <a class="admin-link" href="{{ route('admin.reviews.index') }}">Reviews</a>
                @endif
            </div>
        @endif
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

        {{ $slot }}
    </div>
</div>
</body>
</html>
