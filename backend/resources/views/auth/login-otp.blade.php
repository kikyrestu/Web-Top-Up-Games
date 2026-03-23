<x-layouts.app :title="'Login OTP'">
    <div class="panel" style="max-width:680px; margin:0 auto;">
        <h1>Login OTP</h1>
        <p class="muted">Masuk pakai OTP Email atau WhatsApp. Tidak perlu password.</p>

        <form method="post" action="{{ route('account.login-otp.request') }}" class="grid" style="margin-top:12px;">
            @csrf
            <div>
                <label for="channel">Channel</label>
                <select id="channel" name="channel" required>
                    <option value="EMAIL" @selected(old('channel', 'EMAIL') === 'EMAIL')>EMAIL</option>
                    <option value="WA" @selected(old('channel') === 'WA')>WA</option>
                </select>
            </div>
            <div>
                <label for="destination">Email / Nomor WA</label>
                <input id="destination" name="destination" type="text" value="{{ old('destination') }}" placeholder="email@domain.com atau 628123456789" required>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button class="btn" type="submit">Kirim OTP</button>
            </div>
        </form>
    </div>
</x-layouts.app>
