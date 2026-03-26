<?php
$filepath = __DIR__.'/resources/views/front/index.blade.php';
$content = file_get_contents($filepath);

$search = '<!-- Hero Banner (Static Placeholder) -->
    <div class="w-full bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl h-48 md:h-80 relative overflow-hidden mb-10 shadow-lg flex items-center justify-center">
        <!-- Overlay pattern or image can go here -->
        <div class="absolute inset-0 opacity-20 bg-[url(''https://www.transparenttextures.com/patterns/carbon-fibre.png'')]"></div>
        <div class="relative z-10 text-center text-white px-4">
            <h1 class="text-3xl md:text-5xl font-black italic mb-2 tracking-wide transform -skew-x-6 drop-shadow-md">TOP UP GAME<br><span class="text-yellow-300">CEPAT DAN MURAH</span></h1>
            <p class="md:text-lg font-semibold bg-black/30 inline-block px-4 py-1 rounded-full backdrop-blur-sm mt-2">BUKA 24 JAM, KIAMAT BUKA SETENGAH HARI</p>
        </div>
        <!-- Arrow icons strictly for visual flair -->
        <div class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 bg-black/40 rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-black/60 transition"><i class="fas fa-chevron-left"></i></div>
        <div class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 bg-black/40 rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-black/60 transition"><i class="fas fa-chevron-right"></i></div>
    </div>';

$replace = '<!-- Dynamic Hero Banner -->
    <div x-data="{ activeSlide: 0, slides: {{ count($banners) }} }" x-init="if(slides > 1) setInterval(() => { activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1 }, 5000)" class="w-full rounded-2xl h-48 md:h-80 relative overflow-hidden mb-10 shadow-lg group">
        @forelse($banners as $index => $banner)
            <div x-show="activeSlide === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                @if($banner->link)
                    <a href="{{ $banner->link }}" target="_blank">
                @endif
                <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                @if($banner->link)
                    </a>
                @endif
            </div>
        @empty
            <div class="w-full bg-gradient-to-r from-orange-600 to-red-600 rounded-2xl h-48 md:h-80 relative overflow-hidden mb-10 flex items-center justify-center">
                <div class="absolute inset-0 opacity-20 bg-[url(''https://www.transparenttextures.com/patterns/carbon-fibre.png'')]"></div>
                <div class="relative z-10 text-center text-white px-4">
                    <h1 class="text-3xl md:text-5xl font-black italic mb-2 tracking-wide transform -skew-x-6 drop-shadow-md">TOP UP GAME<br><span class="text-yellow-300">CEPAT DAN MURAH</span></h1>
                    <p class="md:text-lg font-semibold bg-black/30 inline-block px-4 py-1 rounded-full backdrop-blur-sm mt-2">BUKA 24 JAM, KIAMAT BUKA SETENGAH HARI</p>
                </div>
            </div>
        @endforelse

        @if(count($banners) > 1)
            <!-- Arrows -->
            <button @click="activeSlide = activeSlide === 0 ? slides - 1 : activeSlide - 1" class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 bg-black/40 rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-black/80 transition opacity-0 group-hover:opacity-100"><i class="fas fa-chevron-left"></i></button>
            <button @click="activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1" class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 md:w-10 md:h-10 bg-black/40 rounded-full flex items-center justify-center text-white cursor-pointer hover:bg-black/80 transition opacity-0 group-hover:opacity-100"><i class="fas fa-chevron-right"></i></button>
            
            <!-- Indicators -->
            <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                @foreach($banners as $index => $banner)
                    <button @click="activeSlide = {{ $index }}" :class="activeSlide === {{ $index }} ? ''bg-primary w-6'' : ''bg-white/50 w-2''" class="h-2 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>
        @endif
    </div>';

$content = str_replace($search, $replace, $content);
file_put_contents($filepath, $content);
echo "Banner Dynamic Patched";
