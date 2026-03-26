@extends('layouts.front')

@section('title', 'Top Up Game')
@section('meta_description', 'Top up game favorit kamu dengan harga termurah dan proses instan.')
@section('canonical', route('front.top-up-game'))

@section('content')
<div class="container mx-auto px-4 pt-6 pb-16">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-white">Top Up Game</h1>
        <p class="text-up-textmuted text-sm mt-1">Pilih game yang ingin kamu top up. Proses instan & harga bersaing.</p>
    </div>

    @if($categories->count() > 0)
        @php
            $typeLabels = [
                'game' => 'Game Populer',
                'seluler' => 'Game Mobile',
                'pc' => 'Game PC & Console',
                'voucher' => 'Voucher Game',
            ];
        @endphp

        @foreach($grouped as $type => $cats)
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <h2 class="text-lg font-bold text-white">{{ $typeLabels[$type] ?? ucfirst($type) }}</h2>
                    <span class="text-xs text-up-textmuted bg-up-card px-2 py-0.5 rounded-full border border-up-border">{{ $cats->count() }}</span>
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                    @foreach($cats as $category)
                        <a href="{{ route('front.category', $category->slug) }}"
                           class="group bg-up-card rounded-xl border border-up-border hover:border-up-yellow transition-all duration-200 overflow-hidden">
                            <div class="aspect-square relative overflow-hidden">
                                @if($category->thumbnail)
                                    <img src="{{ Storage::url($category->thumbnail) }}" alt="{{ $category->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @elseif($category->icon)
                                    <div class="w-full h-full bg-up-nav flex items-center justify-center">
                                        <img src="{{ Storage::url($category->icon) }}" alt="{{ $category->name }}" class="w-14 h-14 object-contain">
                                    </div>
                                @else
                                    <div class="w-full h-full bg-up-nav flex items-center justify-center">
                                        <i class="fas fa-gamepad text-3xl text-up-textmuted"></i>
                                    </div>
                                @endif

                                @if($category->is_new)
                                    <span class="absolute top-1.5 right-1.5 bg-green-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">BARU</span>
                                @endif
                                @if($category->is_popular)
                                    <span class="absolute top-1.5 left-1.5 bg-up-yellow text-white text-[9px] font-bold px-1.5 py-0.5 rounded">🔥 HOT</span>
                                @endif
                            </div>
                            <div class="p-2.5 text-center">
                                <h3 class="text-xs font-semibold text-white group-hover:text-up-yellow transition-colors truncate">{{ $category->name }}</h3>
                                @if($category->publisher)
                                    <p class="text-[10px] text-up-textmuted mt-0.5 truncate">{{ $category->publisher }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-16 bg-up-card rounded-xl border border-up-border">
            <i class="fas fa-gamepad text-4xl text-up-textmuted mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">Belum Ada Game</h3>
            <p class="text-up-textmuted text-sm">Kategori game akan segera tersedia.</p>
            <a href="{{ route('front.index') }}" class="inline-flex items-center gap-2 mt-4 text-up-yellow hover:text-up-yellowhover font-semibold text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    @endif
</div>
@endsection
