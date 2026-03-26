@extends('layouts.front')
@section('title', $page['title'])
@section('meta_description', $page['description'])
@section('canonical', route('front.page', $slug))
@push('jsonld')
@php
    $pageWebPageSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $page['title'],
        'url' => route('front.page', $slug),
        'description' => $page['description'],
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
                'name' => $page['heading'],
                'item' => route('front.page', $slug),
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($pageWebPageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode($pageBreadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@if($slug === 'faq' && !empty($page['faq_items'] ?? []))
    @push('jsonld')
    @php
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'inLanguage' => 'id-ID',
            'mainEntity' => collect($page['faq_items'])->map(function ($item) {
                return [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            })->values()->all(),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endpush
@endif

@section('content')
<section class="relative pt-28 pb-16 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-[#1c1c1c] via-[#121212] to-[#121212] z-0"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-4xl">
        <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2">
                <li><a href="{{ route('front.index') }}" class="hover:text-white transition">Beranda</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-[#f97316]">{{ $page['heading'] }}</li>
            </ol>
        </nav>

        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">{{ $page['heading'] }}</h1>
        <p class="text-gray-400 text-lg">{{ $page['description'] }}</p>

        <div class="mt-10 bg-[#1c1c1c] border border-gray-800 rounded-2xl p-6 md:p-8 space-y-4">
            @if($slug === 'faq' && !empty($page['faq_items'] ?? []))
                <div class="space-y-4">
                    @foreach($page['faq_items'] as $item)
                        <div class="bg-[#121212] border border-gray-800 rounded-xl p-4">
                            <h2 class="text-white font-semibold mb-2">{{ $item['question'] }}</h2>
                            <p class="text-gray-300 leading-relaxed">{{ $item['answer'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                @foreach($page['content'] as $paragraph)
                    <p class="text-gray-300 leading-relaxed">{{ $paragraph }}</p>
                @endforeach
            @endif

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
