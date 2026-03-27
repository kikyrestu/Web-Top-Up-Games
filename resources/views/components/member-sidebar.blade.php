<div class="bg-gradient-to-br from-[#1d2235] to-[#111620] border border-[#2d3748] rounded-2xl overflow-hidden shadow-[0_0_30px_rgba(0,0,0,0.5)]">
    <!-- User Profile Header -->
    <div class="p-6 border-b border-[#2d3748] flex flex-col items-center text-center">
        <div class="relative mb-4">
            <div class="w-20 h-20 rounded-full border-4 border-[#1d2235] shadow-[0_0_15px_rgba(249,115,22,0.3)] bg-[#2d3748] overflow-hidden flex items-center justify-center">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-user text-3xl text-gray-400"></i>
                @endif
            </div>
            <!-- Verified Badge -->
            @if(auth()->user()->is_verified)
                <div class="absolute bottom-0 right-0 w-6 h-6 bg-blue-500 rounded-full border-2 border-[#1d2235] flex items-center justify-center" title="Verified Account">
                    <i class="fas fa-check text-white text-[10px]"></i>
                </div>
            @endif
        </div>
        
        <h3 class="text-lg font-bold text-white truncate w-full">{{ auth()->user()->name }}</h3>
        <p class="text-xs text-gray-400 mt-1 truncate w-full">{{ auth()->user()->email ?? auth()->user()->whatsapp }}</p>
        
        <!-- Wallet Badge -->
        <div class="mt-4 bg-[#f97316]/10 border border-[#f97316]/20 py-2 px-4 rounded-full flex items-center gap-2">
            <i class="fas fa-wallet text-[#f97316]"></i>
            <span class="text-sm font-bold text-[#f97316]">Rp {{ number_format(auth()->user()->wallet_balance, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="p-4 space-y-1">
        <a href="{{ route('member.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('member.dashboard') ? 'bg-[#f97316] text-white font-bold shadow-lg shadow-[#f97316]/20' : 'text-gray-400 hover:text-white hover:bg-[#2d3748]' }}">
            <i class="fas fa-home w-5 text-center"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="{{ route('member.wallet') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('member.wallet') ? 'bg-[#f97316] text-white font-bold shadow-lg shadow-[#f97316]/20' : 'text-gray-400 hover:text-white hover:bg-[#2d3748]' }}">
            <i class="fas fa-coins w-5 text-center"></i>
            <span>Saldo Wallet</span>
        </a>
        
        <a href="{{ route('member.transactions') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('member.transactions*') ? 'bg-[#f97316] text-white font-bold shadow-lg shadow-[#f97316]/20' : 'text-gray-400 hover:text-white hover:bg-[#2d3748]' }}">
            <i class="fas fa-history w-5 text-center"></i>
            <span>Riwayat Transaksi</span>
            @if(auth()->user()->transactions()->where('payment_status', 'unpaid')->count() > 0)
                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                    {{ auth()->user()->transactions()->where('payment_status', 'unpaid')->count() }}
                </span>
            @endif
        </a>
        
        <a href="{{ route('member.favorites') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('member.favorites') ? 'bg-[#f97316] text-white font-bold shadow-lg shadow-[#f97316]/20' : 'text-gray-400 hover:text-white hover:bg-[#2d3748]' }}">
            <i class="fas fa-heart w-5 text-center"></i>
            <span>Game Favorit</span>
        </a>
        
        <a href="{{ route('member.profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('member.profile') ? 'bg-[#f97316] text-white font-bold shadow-lg shadow-[#f97316]/20' : 'text-gray-400 hover:text-white hover:bg-[#2d3748]' }}">
            <i class="fas fa-user-cog w-5 text-center"></i>
            <span>Pengaturan Profil</span>
        </a>
    </nav>
    
    <!-- Logout -->
    <div class="p-4 border-t border-[#2d3748]">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-all font-semibold">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </button>
        </form>
    </div>
</div>
