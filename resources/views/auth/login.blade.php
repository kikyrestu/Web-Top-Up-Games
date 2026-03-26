<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="text-center mb-6">
        <div class="w-14 h-14 bg-[#f97316]/10 border border-[#f97316]/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-user text-xl text-[#f97316]"></i>
        </div>
        <h2 class="text-xl font-extrabold text-white">Masuk ke Akun</h2>
        <p class="text-sm text-gray-500 mt-1">Top up game & bayar tagihan lebih mudah</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="email@gmail.com">
            @error('email') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="••••••••">
            @error('password') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[#333] bg-[#0f1118] text-[#f97316] shadow-sm focus:ring-[#f97316]" name="remember">
                <span class="ms-2 text-sm text-gray-400">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs text-[#f97316] hover:text-[#ff983f] font-semibold transition" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] transform hover:-translate-y-0.5">
            <i class="fas fa-sign-in-alt mr-2"></i> Masuk
        </button>

        <p class="text-center text-sm text-gray-500 mt-5">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-[#f97316] font-semibold hover:text-[#ff983f] transition">Daftar Sekarang</a>
        </p>
    </form>
</x-guest-layout>
