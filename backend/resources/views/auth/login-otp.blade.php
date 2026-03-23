<x-layouts.app :title="'Login OTP'">
    <div class="panel" style="max-width:680px; margin:0 auto;">
        <h1>Login Customer</h1>
        <p class="muted">Isi data customer lengkap. Jika akun belum ada, sistem akan otomatis membuat akun baru.</p>

        <form method="post" action="{{ route('account.login-otp.request') }}" class="grid" style="margin-top:12px;">
            @csrf
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="email@domain.com" required>
            </div>
            <div>
                <label for="phone_number">Nomor Telepon</label>
                <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number') }}" placeholder="628123456789" required>
            </div>
            <div>
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" placeholder="username_customer" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button class="btn" type="submit">Login / Daftar</button>
            </div>
        </form>
    </div>
</x-layouts.app>
