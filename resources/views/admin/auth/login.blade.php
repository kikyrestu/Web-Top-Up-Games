<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0c0f1a; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .bg-glow { position: fixed; width: 350px; height: 350px; border-radius: 50%; filter: blur(140px); opacity: 0.08; pointer-events: none; }
        .glow-1 { top: -120px; right: -80px; background: #6366f1; }
        .glow-2 { bottom: -120px; left: -80px; background: #4f46e5; }
        .card { background: #141827; border: 1px solid #1e2540; border-radius: 1.25rem; width: 100%; max-width: 420px; overflow: hidden; box-shadow: 0 25px 80px rgba(0,0,0,0.5); }
        .card-header { background: linear-gradient(135deg, #1e1b4b, #312e81); padding: 2rem 2rem 1.5rem; text-align: center; border-bottom: 1px solid #1e2540; }
        .card-header .icon { width: 56px; height: 56px; background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
        .card-header .icon i { font-size: 1.25rem; color: #818cf8; }
        .card-header h1 { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.025em; }
        .card-header p { color: #6b7280; font-size: 0.8rem; margin-top: 0.35rem; }
        .card-body { padding: 1.75rem 2rem 2rem; }
        label { display: block; font-size: 0.75rem; font-weight: 600; color: #9ca3af; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em; }
        input[type="email"], input[type="password"] {
            width: 100%; background: #0f1225; border: 1px solid #1e2540; color: #fff;
            padding: 0.75rem 1rem; border-radius: 0.6rem; font-size: 0.875rem; outline: none; transition: all 0.2s;
        }
        input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        input::placeholder { color: #4b5563; }
        .field + .field { margin-top: 1rem; }
        .remember { display: flex; align-items: center; gap: 0.5rem; margin-top: 1.25rem; }
        .remember input[type="checkbox"] { width: 16px; height: 16px; accent-color: #6366f1; border-radius: 4px; }
        .remember span { font-size: 0.8rem; color: #9ca3af; }
        .btn {
            display: block; width: 100%; margin-top: 1.5rem; padding: 0.8rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff; font-weight: 700; font-size: 0.875rem; border: none; border-radius: 0.6rem; cursor: pointer;
            transition: all 0.3s; box-shadow: 0 0 20px rgba(99,102,241,0.25); text-align: center;
        }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 0 30px rgba(99,102,241,0.4); }
        .error { color: #f87171; font-size: 0.75rem; margin-top: 0.35rem; }
        .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #4ade80; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; margin-bottom: 1rem; }
        @media (max-width: 480px) { .card { margin: 1rem; } }
    </style>
</head>
<body>
    <div class="bg-glow glow-1"></div>
    <div class="bg-glow glow-2"></div>

    <div class="card">
        <div class="card-header">
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <h1>Admin Panel</h1>
            <p>Silakan masuk untuk mengelola sistem</p>
        </div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@example.com">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="remember">
                    <input type="checkbox" id="remember_me" name="remember">
                    <span>Ingat saya</span>
                </div>

                <button type="submit" class="btn"><i class="fas fa-sign-in-alt" style="margin-right:0.5rem"></i> Masuk ke Admin</button>
            </form>
        </div>
    </div>
</body>
</html>
