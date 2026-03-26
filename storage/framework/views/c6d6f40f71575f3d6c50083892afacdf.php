<?php $__env->startSection('title', 'Bayar Tagihan & PPOB'); ?>
<?php $__env->startSection('meta_description', 'Bayar tagihan dan beli pulsa, paket data, token PLN, BPJS, dan layanan PPOB lainnya.'); ?>
<?php $__env->startSection('canonical', route('front.ppob')); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 pt-6 pb-16">

    
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-white">Bayar Tagihan & PPOB</h1>
        <p class="text-up-textmuted text-sm mt-1">Pulsa, paket data, token PLN, dan berbagai layanan PPOB lainnya.</p>
    </div>

    <?php if($categories->count() > 0): ?>
        <?php
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
        ?>

        <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $cats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-4">
                    <h2 class="text-lg font-bold text-white"><?php echo e($typeLabels[$type] ?? ucfirst($type)); ?></h2>
                    <span class="text-xs text-up-textmuted bg-up-card px-2 py-0.5 rounded-full border border-up-border"><?php echo e($cats->count()); ?></span>
                </div>

                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                    <?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('front.category', $category->slug)); ?>"
                           class="group bg-up-card rounded-xl border border-up-border hover:border-up-yellow transition-all duration-200 overflow-hidden">
                            <div class="aspect-square relative overflow-hidden flex items-center justify-center bg-up-nav">
                                <?php if($category->thumbnail): ?>
                                    <img src="<?php echo e(Storage::url($category->thumbnail)); ?>" alt="<?php echo e($category->name); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php elseif($category->icon): ?>
                                    <img src="<?php echo e(Storage::url($category->icon)); ?>" alt="<?php echo e($category->name); ?>" class="w-14 h-14 object-contain">
                                <?php else: ?>
                                    <?php
                                        $slug = strtolower($category->slug);
                                        $matchedIcon = 'fas fa-receipt';
                                        foreach ($ppobIcons as $key => $icon) {
                                            if (str_contains($slug, $key)) {
                                                $matchedIcon = $icon;
                                                break;
                                            }
                                        }
                                    ?>
                                    <i class="<?php echo e($matchedIcon); ?> text-3xl text-up-textmuted"></i>
                                <?php endif; ?>
                            </div>
                            <div class="p-2.5 text-center">
                                <h3 class="text-xs font-semibold text-white group-hover:text-up-yellow transition-colors truncate"><?php echo e($category->name); ?></h3>
                                <?php if($category->description): ?>
                                    <p class="text-[10px] text-up-textmuted mt-0.5 truncate"><?php echo e(Str::limit($category->description, 30)); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="text-center py-16 bg-up-card rounded-xl border border-up-border">
            <i class="fas fa-receipt text-4xl text-up-textmuted mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">Belum Ada Layanan PPOB</h3>
            <p class="text-up-textmuted text-sm">Layanan PPOB akan segera tersedia.</p>
            <a href="<?php echo e(route('front.index')); ?>" class="inline-flex items-center gap-2 mt-4 text-up-yellow hover:text-up-yellowhover font-semibold text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\webppobdantopup\resources\views/front/ppob.blade.php ENDPATH**/ ?>