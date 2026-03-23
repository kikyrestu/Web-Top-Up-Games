<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Web Top-Up Games' }}</title>
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
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 20px;
        }

        .brand {
            text-decoration: none;
            color: var(--ink);
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .nav {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pill {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 8px 14px;
            text-decoration: none;
            color: var(--ink);
            font-weight: 700;
            font-size: 13px;
            background: rgba(255, 255, 255, 0.7);
        }

        .pill:hover { border-color: var(--accent); }

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
            .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="shell">
    <div class="topbar">
        <a class="brand" href="{{ route('storefront.index') }}">TopUp Atlas</a>
        <div class="nav">
            <a class="pill" href="{{ route('storefront.index') }}">Checkout</a>
            <a class="pill" href="{{ route('public.topup.index') }}">Top Up</a>
            <a class="pill" href="{{ route('public.ppob.index') }}">PPOB</a>
            <a class="pill" href="{{ route('public.promo') }}">Promo</a>
            <a class="pill" href="{{ route('public.articles.index') }}">Artikel</a>
            <a class="pill" href="{{ route('public.reviews.index') }}">Ulasan</a>
            <a class="pill" href="{{ route('public.check-transaction') }}">Cek Transaksi</a>
            <a class="pill" href="{{ route('storefront.history') }}">History</a>
            @auth
                <a class="pill" href="{{ route('account.dashboard') }}">Akun Saya</a>
            @else
                <a class="pill" href="{{ route('account.login-otp') }}">Login OTP</a>
            @endauth
            <a class="pill" href="{{ route('admin.dashboard') }}">Admin</a>
            <a class="pill" href="{{ route('admin.dashboard.alerts') }}">Admin Alerts</a>
            <a class="pill" href="{{ route('admin.cms.pages.index') }}">CMS Pages</a>
            <a class="pill" href="{{ route('admin.cms.banners.index') }}">CMS Banners</a>
            <a class="pill" href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>
            <a class="pill" href="{{ route('admin.orders.index') }}">Admin Orders</a>
            <a class="pill" href="{{ route('admin.reviews.index') }}">Review Mod</a>
            @auth
                <form method="post" action="{{ route('account.logout') }}">
                    @csrf
                    <button class="pill" type="submit">Logout</button>
                </form>
            @endauth
        </div>
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
