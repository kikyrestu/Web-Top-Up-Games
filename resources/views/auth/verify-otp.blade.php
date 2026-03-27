<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-14 h-14 bg-[#f97316]/10 border border-[#f97316]/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-shield-alt text-xl text-[#f97316]"></i>
        </div>
        <h2 class="text-xl font-extrabold text-white">Verifikasi OTP</h2>
        <p class="text-sm text-gray-500 mt-1">Kami telah mengirimkan 6 digit kode OTP ke <br> <span class="text-white font-semibold">{{ $target }}</span></p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-500 text-sm p-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('otp.verify') }}">
        @csrf

        <!-- Code -->
        <div class="mt-4">
            <label for="code" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider text-center">Masukkan Kode OTP</label>
            <input id="code" type="text" name="code" required autofocus autocomplete="one-time-code"
                   class="w-full text-center tracking-[1em] text-2xl font-bold bg-[#0f1118] border border-[#2d2d2d] text-white rounded-lg px-4 py-4 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-700"
                   placeholder="------" maxlength="6">
            @error('code') <p class="text-red-400 text-xs mt-1.5 text-center">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] transform hover:-translate-y-0.5">
            <i class="fas fa-check-circle mr-2"></i> Verifikasi Sekarang
        </button>
    </form>

    <div class="mt-6 text-center" x-data="otpCountdown()">
        <p class="text-sm text-gray-500">Belum menerima kode?</p>
        
        <form method="POST" action="{{ route('otp.resend') }}" x-ref="resendForm" class="mt-2">
            @csrf
            <template x-if="remaining > 0">
                <span class="text-sm text-gray-400 font-mono">Tunggu <span x-text="remaining"></span> detik</span>
            </template>
            <template x-if="remaining <= 0">
                <button type="submit" class="text-[#f97316] font-semibold text-sm hover:text-[#ff983f] transition">
                    <i class="fas fa-paper-plane mr-1 text-xs"></i> Kirim Ulang OTP
                </button>
            </template>
        </form>
    </div>

    <script>
        function otpCountdown() {
            return {
                remaining: 60,
                init() {
                    if (sessionStorage.getItem('otp_timer')) {
                        const saved = parseInt(sessionStorage.getItem('otp_timer'));
                        const elapsed = Math.floor((Date.now() - saved) / 1000);
                        this.remaining = Math.max(0, 60 - elapsed);
                    } else {
                        sessionStorage.setItem('otp_timer', Date.now());
                    }
                    
                    const interval = setInterval(() => {
                        if (this.remaining > 0) {
                            this.remaining--;
                        } else {
                            clearInterval(interval);
                        }
                    }, 1000);
                }
            }
        }
    </script>
</x-guest-layout>
