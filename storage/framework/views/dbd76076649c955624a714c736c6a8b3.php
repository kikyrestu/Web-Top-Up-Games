<?php $__env->startSection('title', $category->name . ' - Bayar dan Tagihan'); ?>
<?php $__env->startSection('meta_description', 'Bayar tagihan ' . $category->name . ' cepat, aman, dan otomatis.'); ?>
<?php $__env->startSection('canonical', route('front.category', $category->slug ?? $category->id)); ?>

<?php $__env->startPush('jsonld'); ?>
<script type="application/ld+json">
{
    "<?php echo e('@'); ?>context": "https://schema.org",
    "<?php echo e('@'); ?>type": "WebPage",
    "name": "<?php echo e($category->name); ?> - Bayar dan Tagihan",
    "url": "<?php echo e(route('front.category', $category->slug ?? $category->id)); ?>",
    "description": "Bayar tagihan <?php echo e($category->name); ?> cepat, aman, dan otomatis."
}
</script>
<script type="application/ld+json">
{
    "<?php echo e('@'); ?>context": "https://schema.org",
    "<?php echo e('@'); ?>type": "BreadcrumbList",
    "itemListElement": [
        {
            "<?php echo e('@'); ?>type": "ListItem",
            "position": 1,
            "name": "Beranda",
            "item": "<?php echo e(route('front.index')); ?>"
        },
        {
            "<?php echo e('@'); ?>type": "ListItem",
            "position": 2,
            "name": "<?php echo e($category->name); ?>",
            "item": "<?php echo e(route('front.category', $category->slug ?? $category->id)); ?>"
        }
    ]
}
</script>
<?php
    $ppobProductsSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Daftar produk ' . $category->name,
        'numberOfItems' => $products->count(),
        'itemListElement' => $products->take(20)->values()->map(function ($product, $index) use ($category) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'Product',
                    'name' => $product->name,
                    'sku' => (string) (optional($product->providerMappings->first())->provider_product_code ?? $product->id),
                    'category' => $category->name,
                    'url' => route('front.category', $category->slug ?? $category->id),
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => route('front.category', $category->slug ?? $category->id),
                        'priceCurrency' => 'IDR',
                        'price' => (float) $product->price_sell,
                        'availability' => 'https://schema.org/InStock',
                    ],
                ],
            ];
        })->all(),
    ];
?>
<script type="application/ld+json"><?php echo json_encode($ppobProductsSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 pt-24 md:pt-28 relative z-20">
    <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li><a href="<?php echo e(route('front.index')); ?>" class="hover:text-white transition">Beranda</a></li>
            <li><i class="fas fa-chevron-right text-xs text-gray-500"></i></li>
            <li class="text-[#f97316] font-medium"><?php echo e($category->name); ?></li>
        </ol>
    </nav>
</div>

<div class="container mx-auto px-4 pb-20" x-data="ppobCheckout(<?php echo e(json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price_sell]))); ?>, <?php echo e(json_encode($paymentGateways->map(fn($pg) => ['id' => $pg->id, 'name' => $pg->name]))); ?>)">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-8 flex flex-col gap-5 md:gap-6">

            <!-- Category Header -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] p-5 md:p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-2xl shadow-lg">
                        <i class="<?php echo e($category->icon ?? 'fas fa-file-invoice-dollar'); ?>"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-black text-white"><?php echo e($category->name); ?></h1>
                        <p class="text-gray-400 text-sm mt-0.5"><?php echo e($category->description ?? 'Bayar tagihan cepat dan aman'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Step 1: Input Data -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">1</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Masukkan Data</h2>
                </div>
                <div class="p-4 md:p-6">
                    <?php $__currentLoopData = $formFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-4 last:mb-0">
                        <label class="block text-gray-400 text-xs mb-1.5 font-medium"><?php echo e($field['label'] ?? 'Field'); ?></label>
                        <div class="relative">
                            <input type="text"
                                   x-model="target"
                                   @input="onTargetInput()"
                                   placeholder="<?php echo e($field['placeholder'] ?? ''); ?>"
                                   class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-white placeholder-gray-500 text-sm"
                                   :class="detectedProvider ? 'pr-36' : ''">

                            <!-- Provider Badge (auto-detect) -->
                            <div x-show="detectedProvider" x-transition
                                 class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 bg-[#222] border border-[#444] rounded-lg px-3 py-1.5">
                                <img x-show="providerLogo" :src="providerLogo" :alt="detectedProvider" class="h-5 w-auto object-contain" loading="lazy">
                                <span class="text-white text-xs font-bold" x-text="detectedProvider"></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Step 2: Pilih Produk (Filtered by Provider) -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">2</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Layanan</h2>
                    <span x-show="detectedProvider" x-transition class="text-xs text-green-400 font-medium ml-auto" x-text="'Menampilkan produk ' + detectedProvider"></span>
                </div>
                <div class="p-4 md:p-6">
                    <!-- Prompt to enter number first -->
                    <div x-show="isPulsaMode && !detectedProvider && target.length < 4" class="text-center py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                        <i class="fas fa-mobile-alt text-3xl mb-2 text-gray-600"></i>
                        <p class="text-sm text-gray-400">Masukkan nomor HP di atas untuk menampilkan produk.</p>
                    </div>

                    <!-- Not detected -->
                    <div x-show="isPulsaMode && !detectedProvider && target.length >= 4" class="text-center py-8 bg-[#121212] rounded-lg border border-dashed border-red-900/50">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2 text-red-500/50"></i>
                        <p class="text-sm text-red-400">Provider tidak terdeteksi. Periksa kembali nomor HP Anda.</p>
                    </div>

                    <!-- Product Grid -->
                    <div x-show="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="border border-[#333] hover:border-green-500 bg-[#222] rounded-xl p-3 md:p-4 cursor-pointer relative overflow-hidden group transition-all"
                                 :class="{'border-green-500 bg-green-500/5': selectedProduct === product.id}"
                                 @click="selectProduct(product.id, product.name, product.price)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs md:text-sm font-bold text-gray-200 group-hover:text-white" x-text="product.name"></div>
                                        <div class="text-green-400 font-black text-sm md:text-base mt-1" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></div>
                                    </div>
                                    <div class="text-green-500 scale-0 transition-transform duration-200"
                                         :class="{'scale-100': selectedProduct === product.id}">
                                        <i class="fas fa-check-circle text-lg bg-black rounded-full"></i>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- No products for non-pulsa -->
                    <div x-show="!isPulsaMode && filteredProducts.length === 0" class="text-center py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                        <i class="fas fa-box-open text-3xl mb-2 text-gray-600"></i>
                        <p class="text-sm text-gray-400">Belum ada layanan tersedia.</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Pilih Pembayaran -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">3</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Pembayaran</h2>
                </div>
                <div class="p-4 md:p-6">
                    <div x-show="allPaymentGateways.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <template x-for="pg in allPaymentGateways" :key="pg.id">
                            <div class="border border-[#333] hover:border-green-500 bg-[#222] rounded-xl p-3 md:p-4 cursor-pointer relative group transition-all"
                                 :class="{'border-green-500 bg-green-500/5': selectedPayment === pg.id}"
                                 @click="selectPayment(pg.id, pg.name)">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-xs font-bold" x-text="pg.name.substring(0,2)"></div>
                                    <p class="text-gray-200 font-bold text-sm" x-text="pg.name"></p>
                                </div>
                                <div class="absolute top-3 right-3 text-green-500 scale-0 transition-transform duration-200"
                                     :class="{'scale-100': selectedPayment === pg.id}">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="allPaymentGateways.length === 0" class="text-center text-gray-500 py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                        <p class="text-sm text-gray-400">Metode pembayaran belum dikonfigurasi.</p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Kontak -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">4</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Kontak</h2>
                </div>
                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-xs mb-1.5 font-medium">WhatsApp</label>
                            <input type="text" x-model="whatsapp" placeholder="08xxxxxxxxxx" class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-white placeholder-gray-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs mb-1.5 font-medium">Email (Opsional)</label>
                            <input type="email" x-model="email" placeholder="email@example.com" class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-white placeholder-gray-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Col: Summary -->
        <div class="lg:col-span-4">
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] p-5 md:p-6 sticky top-28">
                <h3 class="text-white text-lg font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-receipt text-green-400"></i> Ringkasan
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-400">
                        <span>Layanan</span>
                        <span class="text-white font-medium" x-text="selectedProductName || '-'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Nomor Tujuan</span>
                        <span class="text-white font-medium" x-text="target || '-'"></span>
                    </div>
                    <div x-show="detectedProvider" class="flex justify-between items-center text-gray-400">
                        <span>Provider</span>
                        <div class="flex items-center gap-2">
                            <img x-show="providerLogo" :src="providerLogo" :alt="detectedProvider" class="h-4 w-auto object-contain" loading="lazy">
                            <span class="text-green-400 font-bold" x-text="detectedProvider"></span>
                        </div>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Harga</span>
                        <span class="text-white font-bold" x-text="selectedProductPrice ? 'Rp ' + new Intl.NumberFormat('id-ID').format(selectedProductPrice) : '-'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Pembayaran</span>
                        <span class="text-white font-medium" x-text="selectedPaymentName || '-'"></span>
                    </div>
                </div>
                <div class="border-t border-[#2d2d2d] mt-4 pt-4">
                    <div class="flex justify-between items-end text-lg">
                        <span class="text-gray-400 font-medium">Total</span>
                        <span class="text-green-400 font-black" x-text="selectedProductPrice ? 'Rp ' + new Intl.NumberFormat('id-ID').format(selectedProductPrice) : '-'"></span>
                    </div>
                </div>
                <button @click="submitCheckout()"
                        :disabled="!canSubmit"
                        :class="canSubmit ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white' : 'bg-[#333] text-gray-500 cursor-not-allowed'"
                        class="w-full mt-5 py-3 rounded-lg font-bold text-sm transition shadow-lg">
                    <span x-show="!isSubmitting"><i class="fas fa-paper-plane mr-2"></i>Bayar Sekarang</span>
                    <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>Memproses...</span>
                </button>
                <p class="text-center text-gray-500 text-[11px] mt-3">
                    <i class="fas fa-lock mr-1"></i> Pembayaran dijamin aman
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const PROVIDER_PREFIXES = {
    'Telkomsel': ['0811','0812','0813','0821','0822','0823','0852','0853'],
    'Indosat': ['0814','0815','0816','0855','0856','0857','0858'],
    'XL': ['0817','0818','0819','0859','0877','0878'],
    'Axis': ['0831','0832','0833','0838'],
    'Tri': ['0895','0896','0897','0898','0899'],
    'Smartfren': ['0881','0882','0883','0884','0885','0886','0887','0888','0889'],
    'By.U': ['0851'],
};

const PROVIDER_LOGOS = {
    'Telkomsel': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Logo_of_Telkomsel_%282021%29.svg/200px-Logo_of_Telkomsel_%282021%29.svg.png',
    'Indosat': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d2/Logo_of_Indosat_Ooredoo_Hutchison.svg/200px-Logo_of_Indosat_Ooredoo_Hutchison.svg.png',
    'XL': 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_of_XL_Axiata.svg/200px-Logo_of_XL_Axiata.svg.png',
    'Axis': 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Logo_of_Axis_Telekom_Indonesia.svg/200px-Logo_of_Axis_Telekom_Indonesia.svg.png',
    'Tri': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/3_%28telecommunications%29_logo_2.svg/200px-3_%28telecommunications%29_logo_2.svg.png',
    'Smartfren': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Logo_of_Smartfren.svg/200px-Logo_of_Smartfren.svg.png',
    'By.U': 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/By.U_logo.svg/200px-By.U_logo.svg.png',
};

const PROVIDER_COLORS = {
    'Telkomsel': 'bg-red-600',
    'Indosat': 'bg-yellow-500',
    'XL': 'bg-blue-600',
    'Axis': 'bg-purple-600',
    'Tri': 'bg-orange-500',
    'Smartfren': 'bg-pink-600',
    'By.U': 'bg-violet-500',
};

const CATEGORY_TYPE = '<?php echo e($category->type ?? "ppob"); ?>';
const PULSA_TYPES = ['pulsa', 'paket_data'];

function detectProvider(number) {
    const clean = number.replace(/\D/g, '');
    if (clean.length < 4) return null;
    const prefix4 = clean.substring(0, 4);
    for (const [provider, prefixes] of Object.entries(PROVIDER_PREFIXES)) {
        if (prefixes.includes(prefix4)) return provider;
    }
    return null;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('ppobCheckout', (allProducts, allPGs) => ({
        target: '',
        targetZone: '',
        whatsapp: '',
        email: '',
        detectedProvider: null,
        providerColor: '',
        providerLogo: '',
        isPulsaMode: PULSA_TYPES.includes(CATEGORY_TYPE),
        allProducts: allProducts,
        allPaymentGateways: allPGs,
        selectedProduct: null,
        selectedProductName: '',
        selectedProductPrice: 0,
        selectedPayment: null,
        selectedPaymentName: '',
        isSubmitting: false,

        get filteredProducts() {
            if (!this.isPulsaMode || !this.detectedProvider) {
                return this.isPulsaMode ? [] : this.allProducts;
            }
            const prov = this.detectedProvider.toLowerCase();
            const filtered = this.allProducts.filter(p => {
                const name = p.name.toLowerCase();
                return name.includes(prov);
            });
            return filtered.length > 0 ? filtered : this.allProducts;
        },

        get canSubmit() {
            return this.target && this.selectedProduct && this.selectedPayment && this.whatsapp && !this.isSubmitting;
        },

        onTargetInput() {
            if (!this.isPulsaMode) return;
            const provider = detectProvider(this.target);
            this.detectedProvider = provider;
            this.providerColor = provider ? (PROVIDER_COLORS[provider] || 'bg-gray-600') : '';
            this.providerLogo = provider ? (PROVIDER_LOGOS[provider] || '') : '';
            this.selectedProduct = null;
            this.selectedProductName = '';
            this.selectedProductPrice = 0;
        },

        selectProduct(id, name, price) {
            this.selectedProduct = id;
            this.selectedProductName = name;
            this.selectedProductPrice = price;
        },

        selectPayment(id, name) {
            this.selectedPayment = id;
            this.selectedPaymentName = name;
        },

        submitCheckout() {
            if (!this.canSubmit) return;
            this.isSubmitting = true;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(route("checkout")); ?>';
            const data = {
                '_token': '<?php echo e(csrf_token()); ?>',
                'target_id': this.target,
                'target_zone': this.targetZone,
                'product_id': this.selectedProduct,
                'payment_gateway_id': this.selectedPayment,
                'quantity': 1,
                'customer_whatsapp': this.whatsapp,
                'customer_email': this.email,
            };
            for (const [key, value] of Object.entries(data)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }
    }));
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\webppobdantopup\resources\views/front/show-ppob.blade.php ENDPATH**/ ?>