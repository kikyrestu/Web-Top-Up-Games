@extends('layouts.front')

@section('title', 'Bayar Tagihan & PPOB')
@section('meta_description', 'Bayar tagihan dan beli pulsa, paket data, token PLN, BPJS, dan layanan PPOB lainnya.')
@section('canonical', route('front.ppob'))

@section('content')
<div class="container mx-auto px-4 pt-6 pb-16">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-white">Bayar Tagihan & PPOB</h1>
        <p class="text-up-textmuted text-sm mt-1">Pulsa, paket data, token PLN, dan berbagai layanan PPOB lainnya.</p>
    </div>

    @if($categories->count() > 0)
        @php
            $typeLabels = [
                'pulsa' => 'Pulsa & Paket Data',
                'ppob' => 'Tagihan & Utilitas',
                'emoney' => 'E-Money & Dompet Digital',
            ];
            $ppobIcons = [
                'pulsa' => 'fas fa-mobile-alt',
                'pln' => 'fas fa-bolt',
                'bpjs' => 'fas fa-heartbeat',
                'internet' => 'fas fa-wifi',
                'tv' => 'fas fa-tv',
                'pdam' => 'fas fa-tint',
                'paket' => 'fas fa-sim-card',
                'token' => 'fas fa-bolt',
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
                                <a href="{{ route('front.category', $category->slug ?? $category->id) }}"
                           class="group bg-up-card rounded-xl border border-up-border hover:border-up-yellow transition-all duration-200 overflow-hidden">
                            <div class="aspect-square relative overflow-hidden flex items-center justify-center bg-up-nav">
                                @if($category->thumbnail)
                                    <img src="{{ Storage::url($category->thumbnail) }}" alt="{{ $category->name }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @elseif($category->icon)
                                    <img src="{{ Storage::url($category->icon) }}" alt="{{ $category->name }}" class="w-14 h-14 object-contain">
                                @else
                                    @php
                                        $slug = strtolower($category->slug);
                                        $matchedIcon = 'fas fa-receipt';
                                        foreach ($ppobIcons as $key => $icon) {
                                            if (str_contains($slug, $key)) {
                                                $matchedIcon = $icon;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <i class="{{ $matchedIcon }} text-3xl text-up-textmuted"></i>
                                @endif
                            </div>
                            <div class="p-2.5 text-center">
                                <h3 class="text-xs font-semibold text-white group-hover:text-up-yellow transition-colors truncate">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-[10px] text-up-textmuted mt-0.5 truncate">{{ Str::limit($category->description, 30) }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-16 bg-up-card rounded-xl border border-up-border">
            <i class="fas fa-receipt text-4xl text-up-textmuted mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">Belum Ada Layanan PPOB</h3>
            <p class="text-up-textmuted text-sm">Layanan PPOB akan segera tersedia.</p>
            <a href="{{ route('front.index') }}" class="inline-flex items-center gap-2 mt-4 text-up-yellow hover:text-up-yellowhover font-semibold text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    @endif
</div>
@endsection
