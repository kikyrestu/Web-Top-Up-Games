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
        }

        .topbar-main {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 0;
        }

        .brand {
            text-decoration: none;
            color: var(--ink);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .quick-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .action-link {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 12px;
            text-decoration: none;
            color: var(--ink);
            font-weight: 800;
            font-size: 12px;
            background: #fff;
        }

        .action-link:hover { border-color: var(--accent); }

        .cta-link {
            border-color: transparent;
            background: linear-gradient(120deg, var(--accent), #0ea26a);
            color: #fff;
        }

        .admin-strip {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            border-top: 1px dashed #bcd4c3;
            padding-top: 10px;
        }

        .admin-link {
            border: 1px solid #bcd4c3;
            border-radius: 999px;
            padding: 7px 11px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 800;
            color: #1f5e3c;
            background: #f4fbf7;
        }

        .logout-btn {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 12px;
            background: #fff;
            color: var(--ink);
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
            .topbar-main {
                align-items: flex-start;
                flex-direction: column;
            }
            .quick-actions { justify-content: flex-start; }
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
        <div class="topbar-main">
            <a class="brand" href="{{ route('storefront.index') }}">TopUp Atlas</a>
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
