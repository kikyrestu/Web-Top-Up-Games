<x-layouts.app :title="'Admin Login'">
    <div class="panel" style="max-width:520px; margin:0 auto;">
        <h1>Admin Sign In</h1>
        <p class="muted">Masuk menggunakan akun admin yang sudah di-bootstrap.</p>

        <form method="post" action="{{ route('admin.login.submit') }}" class="grid" style="margin-top:14px;">
            @csrf
            <div>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <div>
                <button class="btn" type="submit">Login Admin</button>
            </div>
        </form>
    </div>
</x-layouts.app>
