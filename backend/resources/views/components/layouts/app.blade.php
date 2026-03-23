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
            --bg: #f4f7f2;
            --ink: #13221a;
            --ink-soft: #415245;
            --line: #c8d6cb;
            --panel: #ffffff;
            --accent: #0f7b52;
            --accent-2: #ffd166;
            --danger: #b00020;
            --ok: #196d3a;
            --warn: #946200;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background:
                radial-gradient(circle at 10% 15%, #d9f4e7 0%, #f4f7f2 38%),
                radial-gradient(circle at 92% 5%, #fff2cd 0%, transparent 30%),
                var(--bg);
            color: var(--ink);
            font-family: 'Manrope', sans-serif;
        }

        .shell {
            max-width: 1120px;
            margin: 0 auto;
            padding: 24px 16px 48px;
        }

        .topbar {
            display: grid;
            gap: 10px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #0f1a2b, #1a2e4a 62%, #173658);
            border: 1px solid #2c4668;
            border-radius: 18px;
            padding: 12px;
            box-shadow: 0 14px 28px rgba(12, 24, 42, 0.25);
        }

        .top-utility {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid #2e4c73;
            padding-bottom: 8px;
            color: #b8cce5;
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
            color: #f6fbff;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-sub {
            color: #b8cce5;
            font-size: 12px;
            font-weight: 700;
        }

        .topbar-center {
            flex: 1;
            max-width: 420px;
            margin: 0 10px;
        }

        .top-search {
            width: 100%;
            border: 1px solid #4f6888;
            border-radius: 10px;
            background: #12263f;
            color: #e8f3ff;
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
            border: 1px solid #5f7898;
            border-radius: 999px;
            padding: 8px 12px;
            text-decoration: none;
            color: #e8f3ff;
            font-weight: 800;
            font-size: 12px;
            background: #234364;
        }

        .action-link:hover {
            border-color: #80a7d2;
            background: #2a517a;
        }

        .cta-link {
            border-color: transparent;
            background: linear-gradient(120deg, #f39f34, #e36f27);
            color: #fff;
        }

        .locale-pill {
            border: 1px solid #587295;
            border-radius: 999px;
            padding: 6px 10px;
            color: #d6e7fb;
            font-size: 11px;
            font-weight: 800;
            background: rgba(21, 43, 69, 0.9);
            text-decoration: none;
        }

        .market-strip {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .market-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #4b6689;
            border-radius: 12px;
            padding: 7px 10px;
            color: #d6e7fb;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
            background: rgba(18, 39, 63, 0.75);
        }

        .market-link:hover {
            border-color: #89acd3;
            color: #ffffff;
        }

        .market-icon {
            width: 22px;
            height: 22px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #315a84;
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
            border-top: 1px dashed #4f6889;
            padding-top: 10px;
        }

        .admin-link {
            border: 1px solid #5e7ba0;
            border-radius: 999px;
            padding: 7px 11px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            color: #d6e7fb;
            background: #234364;
        }

        .logout-btn {
            border: 1px solid #5f7898;
            border-radius: 999px;
            padding: 8px 12px;
            background: #234364;
            color: #e8f3ff;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            font-family: 'Manrope', sans-serif;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 10px 32px rgba(19, 34, 26, 0.08);
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
            border-color: #a8debc;
            background: #edf9f1;
            color: var(--ok);
        }

        .flash-err {
            border-color: #efb8c0;
            background: #fff2f4;
            color: var(--danger);
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
            background: linear-gradient(120deg, var(--accent), #0ea26a);
            color: #fff;
        }

        .btn-ghost {
            background: #fff;
            border: 1px solid var(--line);
            color: var(--ink);
        }

        input, select {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 11px;
            font-size: 14px;
            background: #fff;
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
            background: #fbfcfb;
        }

        .card .k { color: var(--ink-soft); font-size: 12px; text-transform: uppercase; font-weight: 700; }
        .card .v { font-size: 26px; font-family: 'Space Grotesk', sans-serif; font-weight: 700; }

        @media (max-width: 900px) {
            .cards { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 640px) {
            .cards { grid-template-columns: 1fr; }
            .shell { padding: 16px 12px 36px; }
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
            .topbar { padding: 10px; }
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
</body>
</html>
