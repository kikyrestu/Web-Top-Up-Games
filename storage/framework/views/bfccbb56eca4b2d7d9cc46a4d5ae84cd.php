<?php $__env->startSection('title', 'Top Up Game Cepat & Murah'); ?>
<?php $__env->startSection('meta_description', 'Top up game, voucher, dan pembayaran PPOB cepat dengan harga kompetitif. Proses instan, aman, dan dukungan pelanggan responsif.'); ?>
<?php $__env->startSection('canonical', route('front.index')); ?>
<?php if(!empty($searchQuery ?? null)): ?>
    <?php $__env->startSection('robots', 'noindex,follow'); ?>
<?php endif; ?>

<?php $__env->startPush('jsonld'); ?>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "<?php echo e('@'); ?>type": "WebPage",
    "name": "Top Up Game Cepat & Murah",
    "url": "<?php echo e(route('front.index')); ?>",
    "description": "Top up game, voucher, dan pembayaran PPOB cepat dengan harga kompetitif."
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-[1280px] mx-auto px-4 mt-6 space-y-8">

    <?php if(!empty($searchQuery)): ?>
    <section class="bg-[#161a29] p-5 rounded-xl border border-up-border shadow-lg">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-white text-lg font-bold">Hasil Pencarian</h2>
                <p class="text-gray-400 text-xs mt-0.5">Kata kunci: <span class="text-up-yellow font-semibold"><?php echo e($searchQuery); ?></span></p>
            </div>
            <a href="<?php echo e(route('front.index')); ?>" class="text-xs text-up-yellow font-semibold hover:text-up-yellowhover transition">Reset</a>
        </div>

        <?php if($searchCategories->isEmpty() && $searchProducts->isEmpty()): ?>
            <p class="text-sm text-gray-400">Tidak ada kategori atau produk yang cocok.</p>
        <?php else: ?>
            <?php if($searchCategories->isNotEmpty()): ?>
            <div class="mb-4">
                <h3 class="text-white text-sm font-semibold mb-2">Kategori</h3>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $searchCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('front.category', $category->slug ?? $category->id)); ?>" class="px-3 py-1.5 text-xs rounded-full border border-up-border bg-up-card text-gray-200 hover:border-up-yellow hover:text-up-yellow transition">
                        <?php echo e($category->name); ?>

                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($searchProducts->isNotEmpty()): ?>
            <div>
                <h3 class="text-white text-sm font-semibold mb-2">Produk</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php $__currentLoopData = $searchProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('front.category', $product->category->slug ?? $product->category->id)); ?>" class="block border border-up-border bg-up-card rounded-lg p-3 hover:border-up-yellow transition">
                        <div class="text-white text-sm font-semibold truncate"><?php echo e($product->name); ?></div>
                        <div class="text-up-textmuted text-[11px] mt-1 truncate"><?php echo e($product->category->name ?? '-'); ?></div>
                        <div class="text-up-yellow text-xs font-bold mt-2">Rp <?php echo e(number_format($product->price_sell, 0, ',', '.')); ?></div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <section x-data="{ activeSlide: 0, slides: <?php echo e($banners->count() > 0 ? $banners->count() : 1); ?> }" x-init="if(slides > 1) setInterval(() => { activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1 }, 5000)" class="w-full rounded-2xl overflow-hidden relative shadow-lg bg-gray-900 aspect-[21/9] md:aspect-[24/7]">
        <?php if($banners->isNotEmpty()): ?>
            <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div x-show="activeSlide === <?php echo e($index); ?>" x-transition.opacity.duration.500ms class="absolute inset-0">
                <?php if($banner->media_type === 'image' && $banner->image): ?>
                <img src="<?php echo e(Storage::url($banner->image)); ?>" alt="<?php echo e($banner->title); ?>" loading="eager" fetchpriority="high" decoding="async" class="w-full h-full object-cover">
                <?php elseif($banner->media_type === 'video' && $banner->media_content): ?>
                <video class="w-full h-full object-cover" autoplay muted loop playsinline>
                    <source src="<?php echo e(Storage::url($banner->media_content)); ?>" type="video/mp4">
                </video>
                <?php elseif(in_array($banner->media_type, ['embed', 'html']) && $banner->media_content): ?>
                <iframe class="w-full h-full border-0 bg-transparent" srcdoc="<?php echo e($banner->media_content); ?>" sandbox="allow-scripts allow-same-origin allow-popups"></iframe>
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-gray-900 to-up-nav text-gray-300 text-sm">Media banner tidak tersedia</div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-r from-gray-900 to-up-nav">
                <div class="text-center text-white">
                    <h1 class="text-3xl font-bold mb-2 text-up-yellow">SPARXIE HADIR!</h1>
                    <p class="text-xl">Top Up Sekarang Diskon 10%*</p>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Tokopedia-style PPOB Section - DYNAMIC -->
    <section class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg mb-8"
             x-data="ppobWidget()" x-init="init()">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
            <!-- Left: Bayar & Tagihan Promo Banner -->
            <div class="lg:col-span-5">
                <h2 class="text-white text-lg md:text-xl font-bold mb-4">Bayar & Tagihan</h2>
                <div class="w-full rounded-lg overflow-hidden relative aspect-[21/9] md:aspect-auto md:h-[160px] shadow-md">
                    <?php if($ppobPromoBanner): ?>
                        <?php if($ppobPromoBanner->media_type === 'image' && $ppobPromoBanner->image): ?>
                        <img src="<?php echo e(Storage::url($ppobPromoBanner->image)); ?>" alt="<?php echo e($ppobPromoBanner->title ?? 'Promo'); ?>" class="w-full h-full object-cover rounded-lg">
                        <?php elseif($ppobPromoBanner->media_type === 'video' && $ppobPromoBanner->media_content): ?>
                        <video class="w-full h-full object-cover rounded-lg" autoplay muted loop playsinline>
                            <source src="<?php echo e(Storage::url($ppobPromoBanner->media_content)); ?>" type="video/mp4">
                        </video>
                        <?php elseif(in_array($ppobPromoBanner->media_type, ['embed', 'html']) && $ppobPromoBanner->media_content): ?>
                        <iframe class="w-full h-full border-0 bg-transparent rounded-lg" srcdoc="<?php echo e($ppobPromoBanner->media_content); ?>" sandbox="allow-scripts allow-same-origin allow-popups"></iframe>
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6">
                            <div class="flex-1">
                                <h3 class="text-white font-bold text-lg mb-1 leading-tight">Makin <span class="text-yellow-300">Hemat</span> di <?php echo e($global_site_name ?? 'PPOBKu'); ?></h3>
                                <p class="text-white text-xs mb-3">Bayar tagihan & top up lebih mudah</p>
                                <a href="#" class="inline-block bg-transparent border-2 border-white text-white text-xs font-semibold px-4 py-1.5 rounded-full hover:bg-white hover:text-green-600 transition">Cek Sekarang</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6">
                        <div class="flex-1">
                            <h3 class="text-white font-bold text-lg mb-1 leading-tight">Makin <span class="text-yellow-300">Hemat</span> di <?php echo e($global_site_name ?? 'PPOBKu'); ?></h3>
                            <p class="text-white text-xs mb-3">Bayar tagihan & top up lebih mudah</p>
                            <a href="#" class="inline-block bg-transparent border-2 border-white text-white text-xs font-semibold px-4 py-1.5 rounded-full hover:bg-white hover:text-green-600 transition">Cek Sekarang</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Top Up & Tagihan (Dynamic Tabs) -->
            <div class="lg:col-span-7 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-white text-lg md:text-xl font-bold">Top Up & Tagihan</h2>
                    <?php if($ppobCategories->isNotEmpty()): ?>
                    <a href="#product-grid" class="text-up-yellow text-sm font-semibold hover:underline">Lihat Semua</a>
                    <?php endif; ?>
                </div>
                <div class="border border-up-border rounded-lg bg-up-card p-4 flex-1 flex flex-col justify-center">
                    <!-- Dynamic Tabs from DB categories -->
                    <div class="flex items-center space-x-6 border-b border-up-border mb-4 overflow-x-auto hide-scroll pb-2 text-sm font-medium">
                        <?php $__empty_1 = true; $__currentLoopData = $ppobCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $ppobCat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <button @click="selectTab(<?php echo e($ppobCat->id); ?>, '<?php echo e($ppobCat->slug ?? $ppobCat->id); ?>')"
                                :class="activeTab === <?php echo e($ppobCat->id); ?> ? 'text-up-yellow border-b-2 border-up-yellow' : 'text-gray-400 hover:text-gray-200'"
                                class="pb-2 whitespace-nowrap px-1 transition">
                            <?php echo e($ppobCat->name); ?>

                        </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="text-gray-500 text-sm">Belum ada kategori PPOB.</span>
                        <?php endif; ?>
                        <?php if($ppobCategories->count() > 4): ?>
                        <button class="text-gray-400 hover:text-gray-200 pb-2 whitespace-nowrap px-1 transition"><i class="fas fa-ellipsis-v"></i></button>
                        <?php endif; ?>
                    </div>
                    <!-- Form -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <label class="block text-gray-400 text-xs mb-1 font-medium">Nomor Tujuan</label>
                            <input type="text" x-model="targetNumber" placeholder="Masukan Nomor" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow transition">
                        </div>
                        <div class="flex-1">
                            <label class="block text-gray-400 text-xs mb-1 font-medium">Nominal</label>
                            <select x-model="selectedProduct" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow appearance-none transition">
                                <option value="">Pilih Nominal</option>
                                <template x-for="product in products" :key="product.id">
                                    <option :value="product.id" x-text="product.name + ' - Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <a :href="activeCategorySlug ? '/kategori/' + activeCategorySlug : '#'"
                               class="w-full sm:w-auto bg-[#343b54] text-gray-300 font-semibold rounded-lg px-6 py-2.5 text-sm hover:bg-up-yellow hover:text-black transition text-center block">Beli</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Category Pills from DB -->
        <div class="flex overflow-x-auto hide-scroll space-x-3 pb-2 pt-2">
            <?php $__currentLoopData = $allGames->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('front.category', $cat->slug ?? $cat->id)); ?>" class="flex items-center flex-shrink-0 space-x-2 border border-up-border bg-up-card rounded-full px-4 py-2 text-xs font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition">
                <i class="<?php echo e($cat->icon ?? 'fas fa-tag'); ?> text-up-yellow"></i> <span><?php echo e($cat->name); ?></span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>

    <section id="product-grid" class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg scroll-mt-20">
        <div class="flex items-center mb-5 border-b border-up-border pb-4">
            <i class="fas fa-fire text-up-yellow text-2xl mr-3 bg-up-yellow/10 p-2.5 rounded-lg"></i>
            <div>
                <h2 class="text-white text-xl font-bold">Game Populer</h2>
                <p class="text-gray-400 text-sm mt-0.5">Top up game pilihan paling banyak dicari</p>
            </div>
        </div>

        <div class="flex overflow-x-auto hide-scroll space-x-3 pb-2">
            <?php $__empty_1 = true; $__currentLoopData = $popularGames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('front.category', $game->slug ?? $game->id)); ?>" class="min-w-[140px] w-[140px] md:min-w-[160px] md:w-[160px] flex-shrink-0 group block border-2 border-transparent hover:border-up-yellow rounded overflow-hidden transition-colors relative bg-up-card shadow-[0_4px_10px_rgba(0,0,0,0.3)]">
                <div class="aspect-[3/4] w-full relative">
                    <img src="<?php echo e($game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&size=300&background=242a40&color=fff'); ?>" class="w-full h-full object-cover" alt="<?php echo e($game->name); ?>" loading="lazy" decoding="async">
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/90 via-black/50 to-transparent">
                        <h3 class="text-white text-xs font-bold leading-tight"><?php echo e($game->name); ?></h3>
                    </div>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-gray-400 text-sm">Belum ada game populer.</div>
            <?php endif; ?>
        </div>
    </section>

    <?php
        $gameTypes = ['game', 'seluler', 'pc', 'voucher'];
        $ppobCategories = $allGames->filter(fn($c) => !in_array(strtolower((string) $c->type), $gameTypes));
        $gameCategories = $allGames->filter(fn($c) => in_array(strtolower((string) $c->type), $gameTypes));
    ?>

    <section class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg mb-12">
        <div class="flex items-center mb-6 border-b border-up-border pb-4">
            <i class="fas fa-gamepad text-up-yellow text-2xl mr-3 bg-up-yellow/10 p-2.5 rounded-lg"></i>
            <div>
                <h2 class="text-white text-lg md:text-xl font-bold">Semua Game</h2>
                <p class="text-gray-400 text-sm mt-0.5">Pilih game dan layanan yang tersedia</p>
            </div>
        </div>

        <?php if($gameCategories->isNotEmpty()): ?>
        <div class="flex items-center space-x-2.5 overflow-x-auto hide-scroll pb-2 mb-6">
            <?php $__currentLoopData = $gameCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('front.category', $category->slug ?? $category->id)); ?>" class="flex items-center flex-shrink-0 space-x-1.5 border border-up-border bg-up-card rounded-full px-4 py-1.5 text-xs font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap">
                <span class="text-up-yellow text-base"><i class="<?php echo e($category->icon ?? 'fas fa-gamepad'); ?>"></i></span>
                <span><?php echo e($category->name); ?></span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            <?php $__currentLoopData = $gameCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('front.category', $game->slug ?? $game->id)); ?>" class="block bg-up-card rounded overflow-hidden group hover:-translate-y-1 transition-transform relative border border-transparent hover:border-up-yellow">
                <?php if($game->is_new): ?>
                <div class="absolute top-0 right-0 bg-up-yellow text-black text-[9px] font-bold px-1.5 py-0.5 rounded-bl z-10">New</div>
                <?php endif; ?>
                <div class="aspect-square w-full relative bg-gray-800">
                    <img src="<?php echo e($game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&size=300&background=242a40&color=fff'); ?>" class="w-full h-full object-cover" alt="<?php echo e($game->name); ?>" loading="lazy" decoding="async">
                </div>
                <div class="p-3">
                    <h3 class="text-white text-[13px] font-bold truncate"><?php echo e($game->name); ?></h3>
                    <p class="text-up-textmuted text-[10px] mt-0.5 font-medium uppercase truncate"><?php echo e($game->publisher ?? 'Developer'); ?></p>
                </div>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <div class="border border-dashed border-up-border rounded-lg text-center py-10 text-up-textmuted text-sm">
            Belum ada kategori game aktif.
        </div>
        <?php endif; ?>
    </section>

    <!-- PPOB & Tagihan Section (Dipindah ke atas) -->
    <section class="bg-[#161a29] p-5 rounded-lg border border-up-border shadow-sm mb-12">
        <div class="flex justify-between items-end mb-5 border-b border-up-border pb-3">
            <div>
                <h2 class="text-white text-lg font-bold">Promo dan Acara</h2>
                <p class="text-up-textmuted text-xs mt-1">Berita dan panduan game terbaru</p>
            </div>
            <a href="<?php echo e(route('front.article.index')); ?>" class="bg-[#343b54] text-gray-300 text-[10px] font-bold px-3 py-1.5 rounded hover:bg-gray-600 transition">Lainnya <i class="fas fa-caret-right ml-1"></i></a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if($latestArticles->isNotEmpty()): ?>
                <?php $__currentLoopData = $latestArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('front.article.show', $article->slug)); ?>" class="block bg-up-card rounded overflow-hidden group border border-transparent hover:border-up-yellow transition-colors relative">
                    <div class="aspect-[24/9] w-full bg-gray-800 relative overflow-hidden">
                        <img src="<?php echo e($article->image ? asset('storage/'.$article->image) : 'https://ui-avatars.com/api/?name=Promo&background=1d2235&color=fff'); ?>" alt="<?php echo e($article->title); ?>" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-up-textmuted text-[10px] font-bold tracking-widest uppercase">PROMO</span>
                            <span class="text-up-textmuted text-[10px]"><?php echo e($article->created_at->format('d M Y')); ?></span>
                        </div>
                        <h3 class="text-white text-sm font-bold line-clamp-2 leading-snug group-hover:text-up-yellow transition"><?php echo e($article->title); ?></h3>
                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="col-span-full border border-dashed border-up-border rounded-lg text-center py-10 text-up-textmuted text-sm">
                    Belum ada promo & acara saat ini.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ppobWidget', () => ({
        activeTab: <?php echo e($ppobCategories->first()->id ?? 0); ?>,
        activeCategorySlug: '<?php echo e($ppobCategories->first()->slug ?? $ppobCategories->first()->id ?? ''); ?>',
        targetNumber: '',
        selectedProduct: '',
        products: [],

        init() {
            if (this.activeTab) {
                this.fetchProducts(this.activeTab);
            }
        },

        selectTab(id, slug) {
            this.activeTab = id;
            this.activeCategorySlug = slug;
            this.selectedProduct = '';
            this.fetchProducts(id);
        },

        fetchProducts(categoryId) {
            fetch('/api/ppob/products?category_id=' + categoryId)
                .then(r => r.json())
                .then(data => { this.products = data; })
                .catch(() => { this.products = []; });
        }
    }));
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\webppobdantopup\resources\views/front/index.blade.php ENDPATH**/ ?>