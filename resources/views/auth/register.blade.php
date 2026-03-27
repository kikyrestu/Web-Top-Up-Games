<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-14 h-14 bg-[#f97316]/10 border border-[#f97316]/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-user-plus text-xl text-[#f97316]"></i>
        </div>
        <h2 class="text-xl font-extrabold text-white">Buat Akun Baru</h2>
        <p class="text-sm text-gray-500 mt-1">Daftar gratis untuk pengalaman top up terbaik</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="Nama kamu">
            @error('name') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- WhatsApp -->
        <div class="mt-4">
            <label for="whatsapp" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Nomor WhatsApp</label>
            <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" required autocomplete="tel"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="08123456789">
            @error('whatsapp') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="email@gmail.com">
            @error('email') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="Minimal 8 karakter">
            @error('password') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block text-xs font-semibold text-gray-400 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full bg-[#0f1118] border border-[#2d2d2d] text-white text-sm rounded-lg px-4 py-3 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition placeholder-gray-600"
                   placeholder="Ulangi password">
            @error('password_confirmation') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full mt-6 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold py-3 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] transform hover:-translate-y-0.5">
            <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
        </button>

        <p class="text-center text-sm text-gray-500 mt-5">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-[#f97316] font-semibold hover:text-[#ff983f] transition">Masuk</a>
        </p>
    </form>
</x-guest-layout>
