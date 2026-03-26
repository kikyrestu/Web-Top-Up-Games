<?php
$indexHtml = <<<'HTML'
@extends('layouts.front')
@section('title', 'Top Up Game Cepat & Murah')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-6">

    <!-- Hero Banner -->
    <div x-data="{ activeSlide: 0, slides: {{ count($banners) > 0 ? count($banners) : 1 }} }" x-init="if(slides > 1) setInterval(() => { activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1 }, 5000)" class="w-full rounded overflow-hidden relative shadow-lg bg-gray-900 aspect-[21/9] md:aspect-[24/7]">
        @forelse($banners as $index => $banner)
            <div x-show="activeSlide === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
            </div>
        @empty
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-r from-gray-900 to-up-nav">
                <div class="text-center text-white">
                    <h1 class="text-3xl font-bold mb-2 text-up-yellow">SPARXIE HADIR!</h1>
                    <p class="text-xl">Top Up Sekarang Diskon 10%*</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Info Bar / Value Props -->
    <div class="mt-8 bg-[#171b2a] border border-up-border/60 rounded flex flex-col md:flex-row justify-between items-center px-8 py-5 shadow-sm">
        <div class="flex items-center space-x-4 mb-4 md:mb-0">
            <div class="w-12 h-12 flex items-center justify-center -ml-2">
                <img src="https://cdns.iconmonstr.com/wp-content/releases/preview/2012/240/iconmonstr-time-2.png" class="w-8 invert" style="filter: brightness(0) saturate(100%) invert(58%) sepia(87%) saturate(1637%) hue-rotate(345deg) brightness(101%) contrast(97%);">
            </div>
            <div>
                <h4 class="text-white text-sm font-bold">Isi ulang instan</h4>
                <p class="text-[10px] text-up-textmuted mt-0.5">Isi ulang instan untuk aksi tanpa henti</p>
            </div>
        </div>
        <div class="hidden md:block w-px h-8 bg-up-border"></div>
        <div class="flex items-center space-x-4 mb-4 md:mb-0">
            <div class="w-12 h-12 flex items-center justify-center -ml-2">
                <img src="https://cdns.iconmonstr.com/wp-content/releases/preview/2012/240/iconmonstr-gift-2.png" class="w-8 invert" style="filter: brightness(0) saturate(100%) invert(58%) sepia(87%) saturate(1637%) hue-rotate(345deg) brightness(101%) contrast(97%);">
            </div>
            <div>
                <h4 class="text-white text-sm font-bold">Dapatkan hadiah besar</h4>
                <p class="text-[10px] text-up-textmuted mt-0.5">Bonus eksklusif untuk meningkatkan permainan Anda</p>
            </div>
        </div>
        <div class="hidden md:block w-px h-8 bg-up-border"></div>
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 flex items-center justify-center -ml-2">
                <img src="https://cdns.iconmonstr.com/wp-content/releases/preview/2012/240/iconmonstr-shield-20.png" class="w-8 invert" style="filter: brightness(0) saturate(100%) invert(58%) sepia(87%) saturate(1637%) hue-rotate(345deg) brightness(101%) contrast(97%);">
            </div>
            <div>
                <h4 class="text-white text-sm font-bold">Terpercaya</h4>
                <p class="text-[10px] text-up-textmuted mt-0.5">Pembayaran aman</p>
            </div>
        </div>
    </div>

    <!-- Sections Generator (Populer, Seluler, PC) -->
    @php
        // Dummy split just to replicate the sections based on $categories 
        $populer = $categories->take(6);
        $seluler = $categories->skip(0)->take(5);
        $pc = $categories->skip(2)->take(5);
        $voucher = $categories->skip(5)->take(5);
    @endphp

    <!-- GAME POPULER -->
    <div class="mt-12">
        <h2 class="text-white text-xl font-bold mb-5 flex items-center">Game Populer</h2>
        <div class="flex overflow-x-auto hide-scroll space-x-3 pb-4">
            @foreach($populer as $game)
                <a href="{{ route('front.category', $game->id) }}" class="min-w-[140px] w-[140px] md:min-w-[160px] md:w-[160px] flex-shrink-0 group block border-2 border-transparent hover:border-up-yellow rounded overflow-hidden transition-colors relative bg-up-card shadow-[0_4px_10px_rgba(0,0,0,0.3)]">
                    <div class="aspect-[3/4] w-full relative">
                        <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&size=300&background=242a40&color=fff' }}" class="w-full h-full object-cover">
                        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/90 via-black/50 to-transparent">
                            <h3 class="text-white text-xs font-bold leading-tight">{{ $game->name }}</h3>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- GAME SELULER -->
    <div class="mt-10 bg-[#161a29] p-5 rounded-lg border border-up-border shadow-sm">
        <div class="flex justify-between items-center mb-5 border-b border-up-border pb-3">
            <h2 class="text-white text-lg font-bold">Game Seluler</h2>
            <a href="#" class="text-up-yellow text-xs font-semibold hover:text-up-yellowhover transition">Lainnya <i class="fas fa-chevron-right text-[10px] ml-1"></i></a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 relative">
            @foreach($seluler as $idx => $game)
                <a href="{{ route('front.category', $game->id) }}" class="block bg-up-card rounded overflow-hidden group hover:-translate-y-1 transition-transform relative border border-transparent hover:border-up-yellow">
                    <!-- Tag badge -->
                    @if($idx % 2 == 0) <div class="absolute top-0 right-0 bg-up-yellow text-black text-[9px] font-bold px-1.5 py-0.5 rounded-bl z-10">New</div> @endif
                    
                    <div class="aspect-square w-full relative bg-gray-800">
                        <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name) }}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-3">
                        <h3 class="text-white text-[13px] font-bold truncate">{{ $game->name }}</h3>
                        <p class="text-up-textmuted text-[10px] mt-0.5 font-medium uppercase truncate">Tencent / Moonton</p>
                        
                        <div class="mt-3">
                            <span class="text-[10px] text-gray-500 line-through block mb-[-2px]">IDR 16.000</span>
                            <span class="text-up-yellow text-xs font-bold block mt-1"><span class="text-gray-400 text-[10px] font-normal mr-1">Check Your</span>Best Price</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6 text-center">
            <button class="text-up-textmuted text-xs border border-up-border px-8 py-2 rounded font-semibold hover:bg-up-card transition">Lebih Banyak</button>
        </div>
    </div>

    <!-- GAME PC -->
    <div class="mt-10 bg-[#161a29] p-5 rounded-lg border border-up-border shadow-sm">
        <div class="flex justify-between items-center mb-5 border-b border-up-border pb-3">
            <h2 class="text-white text-lg font-bold">Game PC</h2>
            <a href="#" class="text-up-yellow text-xs font-semibold hover:text-up-yellowhover transition">Lainnya <i class="fas fa-chevron-right text-[10px] ml-1"></i></a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach($pc as $idx => $game)
                <a href="{{ route('front.category', $game->id) }}" class="block bg-up-card rounded overflow-hidden group hover:-translate-y-1 transition-transform relative border border-transparent hover:border-up-yellow">
                    <div class="aspect-square w-full relative bg-gray-800">
                        <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name) }}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-3">
                        <h3 class="text-white text-[13px] font-bold truncate">{{ $game->name }}</h3>
                        <p class="text-up-textmuted text-[10px] mt-0.5 font-medium uppercase truncate">Publisher</p>
                        
                        <div class="mt-3">
                            <span class="text-up-yellow text-xs font-bold block mt-1">IDR 15.000</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- VOUCHER GAME -->
    <div class="mt-10 bg-[#161a29] p-5 rounded-lg border border-up-border shadow-sm mb-12">
        <div class="flex justify-between items-center mb-5 border-b border-up-border pb-3">
            <h2 class="text-white text-lg font-bold">Voucher Game</h2>
            <a href="#" class="text-up-yellow text-xs font-semibold hover:text-up-yellowhover transition">Lainnya <i class="fas fa-chevron-right text-[10px] ml-1"></i></a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach($voucher as $idx => $game)
                <a href="{{ route('front.category', $game->id) }}" class="block bg-up-card rounded overflow-hidden group hover:-translate-y-1 transition-transform relative border border-transparent hover:border-up-yellow">
                    <div class="aspect-[4/3] w-full relative bg-gray-200">
                        <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&color=000&background=fff' }}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-3">
                        <h3 class="text-white text-[13px] font-bold truncate">{{ $game->name }}</h3>
                        <p class="text-up-textmuted text-[10px] mt-0.5 font-medium uppercase truncate">Google / Steam</p>
                        <div class="mt-3">
                            <span class="text-up-yellow text-xs font-bold block mt-1">Mulai IDR 5.000</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

</div>
@endsection
HTML;
file_put_contents('resources/views/front/index.blade.php', $indexHtml);
echo "INDEX_UPDATED";
?>
