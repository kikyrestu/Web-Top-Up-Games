<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Kesalahan Server</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #121212;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .bg-glow {
            position: fixed;
            width: 300px; height: 300px;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.12;
            pointer-events: none;
        }
        .glow-1 { top: -100px; right: -50px; background: #ef4444; }
        .glow-2 { bottom: -100px; left: -50px; background: #f97316; }
        .container {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 10;
            max-width: 520px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            font-style: italic;
            line-height: 1;
            background: linear-gradient(135deg, #ef4444, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.75rem; }
        p { color: #9ca3af; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff; font-weight: 700; font-size: 0.875rem;
            padding: 0.875rem 2rem; border-radius: 0.75rem;
            text-decoration: none; transition: all 0.3s;
            box-shadow: 0 0 20px rgba(249,115,22,0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 30px rgba(249,115,22,0.5);
        }
        .icon-wrap {
            width: 80px; height: 80px;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .icon-wrap i { font-size: 2rem; color: #ef4444; }
    </style>
</head>
<body>
    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>
    <div class="container">
        <div class="icon-wrap"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="error-code">500</div>
        <h1>Terjadi Kesalahan Server</h1>
        <p>Maaf, ada yang tidak beres di sisi kami. Tim teknis sudah diberitahu. Silakan coba lagi dalam beberapa saat.</p>
        <a href="/" class="btn"><i class="fas fa-home"></i> Kembali ke Beranda</a>
    </div>
</body>
</html>
