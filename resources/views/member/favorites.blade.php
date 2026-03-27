@extends('layouts.front')

@section('title', 'Game Favorit')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <x-member-sidebar />
        </div>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4">
            
            <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl shadow-lg overflow-hidden min-h-[600px] flex flex-col">
                <div class="p-6 border-b border-[#2d3748] bg-[#252b43] flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-heart text-pink-500 mr-2"></i> Daftar Game Favorit</h2>
                    <span class="text-sm text-gray-400 bg-[#1d2235] px-3 py-1 rounded-lg border border-[#2d3748]">{{ $favorites->count() }} Game</span>
                </div>

                <div class="p-6 flex-grow">
                    @if($favorites->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($favorites as $fav)
                                <div class="bg-[#252b43] border border-[#2d3748] rounded-2xl overflow-hidden hover:border-[#f97316] hover:-translate-y-1 transition-all duration-300 group shadow-lg flex flex-col relative" id="fav-card-{{ $fav->category_id }}">
                                    <!-- Remove Favorite Button -->
                                    <button 
                                        onclick="removeFavorite({{ $fav->category_id }})"
                                        class="absolute top-2 right-2 w-8 h-8 bg-black/60 backdrop-blur border border-white/10 rounded-full flex items-center justify-center text-pink-500 hover:bg-pink-500 hover:text-white transition-colors z-10"
                                        title="Hapus dari Favorit"
                                    >
                                        <i class="fas fa-heart"></i>
                                    </button>

                                    <a href="{{ route('front.category', $fav->category->slug ?? $fav->category->id) }}" class="block flex-1">
                                        <!-- Image -->
                                        <div class="aspect-square w-full overflow-hidden bg-[#1d2235]">
                                            @if($fav->category->thumbnail)
                                                <img src="{{ asset('storage/' . $fav->category->thumbnail) }}" alt="{{ $fav->category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <i class="fas fa-gamepad text-4xl text-gray-500 group-hover:text-[#f97316] transition-colors"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="p-3 text-center">
                                            <h3 class="text-white text-xs font-bold leading-tight line-clamp-2 group-hover:text-[#f97316] transition-colors">{{ $fav->category->name }}</h3>
                                            @if($fav->category->brand)
                                                <p class="text-[10px] text-gray-500 mt-1 truncate">{{ $fav->category->brand }}</p>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-center py-20">
                            <div class="w-24 h-24 mb-6 rounded-full bg-[#252b43] border border-[#2d3748] flex items-center justify-center">
                                <i class="fas fa-heart-broken text-4xl text-gray-500"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">Belum ada game favorit</h3>
                            <p class="text-gray-400 text-sm max-w-sm mb-6">Tambahkan game ke daftar favorit dengan menekan tombol hati pada halaman top up game, agar lebih mudah dicari nanti.</p>
                            <a href="{{ route('front.index') }}" class="px-6 py-3 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold rounded-xl shadow-[0_0_20px_rgba(249,115,22,0.3)] transition-all">
                                Cari Game Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function removeFavorite(categoryId) {
        if(confirm('Hapus game ini dari daftar favorit?')) {
            fetch(`/member/favorites/${categoryId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success && !data.is_favorite) {
                    const card = document.getElementById('fav-card-' + categoryId);
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem.');
            });
        }
    }
</script>
@endpush
@endsection
