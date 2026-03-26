<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-14 h-14 bg-[#f97316]/10 border border-[#f97316]/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-key text-xl text-[#f97316]"></i>
        </div>
        <h2 class="text-xl font-extrabold text-white">Lupa Password?</h2>
        <p class="text-sm text-gray-500 mt-1">Masukkan email untuk mendapatkan link reset password.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="email@gmail.com">
            @error('email') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] transform hover:-translate-y-0.5">
            <i class="fas fa-paper-plane mr-2"></i> Kirim Link Reset
        </button>

        <p class="text-center text-sm text-gray-500 mt-5">
            <a href="{{ route('login') }}" class="text-[#f97316] font-semibold hover:text-[#ff983f] transition"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Login</a>
        </p>
    </form>
</x-guest-layout>
