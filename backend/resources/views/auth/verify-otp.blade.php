<x-layouts.app :title="'Verifikasi OTP'">
    <div class="panel" style="max-width:680px; margin:0 auto;">
        <h1>Verifikasi OTP</h1>
        <p class="muted">OTP telah dikirim ke {{ $pending['channel'] ?? '-' }}: <strong>{{ $pending['destination'] ?? '-' }}</strong>.</p>

        <form method="post" action="{{ route('account.verify-otp.submit') }}" class="grid" style="margin-top:12px;">
            @csrf
            <div>
                <label for="code">Kode OTP (6 digit)</label>
                <input id="code" name="code" type="text" maxlength="6" inputmode="numeric" placeholder="123456" required>
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <a class="pill" href="{{ route('account.login-otp') }}">Kembali</a>
                <button class="btn" type="submit">Verifikasi</button>
            </div>
        </form>
    </div>
</x-layouts.app>
