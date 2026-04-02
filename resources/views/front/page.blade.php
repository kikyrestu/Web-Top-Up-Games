@extends('layouts.front')
@section('title', $page->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($page->content), 150))
@section('canonical', route('front.page', $slug))
@push('jsonld')
@php
    $pageWebPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $page->title,
        'url' => route('front.page', $slug),
        'description' => \Illuminate\Support\Str::limit(strip_tags($page->content), 150),
    ];

    $pageBreadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Beranda',
                'item' => route('front.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $page->title,
                'item' => route('front.page', $slug),
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($pageWebPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($pageBreadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<section class="relative pt-28 pb-16 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-[#1c1c1c] via-[#121212] to-[#121212] z-0"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-4xl">
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('front.index') }}" class="hover:text-white transition">Beranda</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-[#f97316]">{{ $page->title }}</li>
            </ol>
        </nav>

        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">{{ $page->title }}</h1>

        <div class="mt-10 bg-[#1c1c1c] border border-gray-800 rounded-2xl p-6 md:p-8 space-y-4 prose prose-invert max-w-none">
            {!! $page->content !!}

            @if($slug === 'kontak')
                <div class="mt-4 pt-4 border-t border-gray-800 space-y-2">
                    @if(!empty($contactWhatsapp))
                        <p class="text-sm text-gray-300">WhatsApp: <span class="text-[#f97316] font-semibold">{{ $contactWhatsapp }}</span></p>
                    @endif
                    @if(!empty($contactEmail))
                        <p class="text-sm text-gray-300">Email: <span class="text-[#f97316] font-semibold">{{ $contactEmail }}</span></p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
