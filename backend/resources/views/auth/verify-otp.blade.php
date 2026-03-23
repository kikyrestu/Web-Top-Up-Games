<x-layouts.app :title="'Verifikasi OTP'">
    <div class="panel" style="max-width:680px; margin:0 auto; text-align:center;">
        <h1>Verifikasi OTP Dinonaktifkan</h1>
        <p class="muted">Silakan login menggunakan data customer: email, nomor telepon, username, dan password.</p>
        <div style="margin-top:12px; display:flex; justify-content:center;">
            <a class="btn" href="{{ route('account.login-otp') }}">Kembali ke Login Customer</a>
        </div>
    </div>
</x-layouts.app>
