<?php $__env->startSection('title', 'Top Up Game'); ?>
<?php $__env->startSection('meta_description', 'Top up game favorit kamu dengan harga termurah dan proses instan.'); ?>
<?php $__env->startSection('canonical', route('front.top-up-game')); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 pt-6 pb-16">

    
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-extrabold text-white">Top Up Game</h1>
        <p class="text-up-textmuted text-sm mt-1">Pilih game yang ingin kamu top up. Proses instan & harga bersaing.</p>
    </div>

    <?php if($categories->count() > 0): ?>
        <?php
            $typeLabels = [
                'game' => 'Game Populer',
                'seluler' => 'Game Mobile',
                'pc' => 'Game PC & Console',
                'voucher' => 'Voucher Game',
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
                            <div class="aspect-square relative overflow-hidden">
                                <?php if($category->thumbnail): ?>
                                    <img src="<?php echo e(Storage::url($category->thumbnail)); ?>" alt="<?php echo e($category->name); ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                <?php elseif($category->icon): ?>
                                    <div class="w-full h-full bg-up-nav flex items-center justify-center">
                                        <img src="<?php echo e(Storage::url($category->icon)); ?>" alt="<?php echo e($category->name); ?>" class="w-14 h-14 object-contain">
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full bg-up-nav flex items-center justify-center">
                                        <i class="fas fa-gamepad text-3xl text-up-textmuted"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if($category->is_new): ?>
                                    <span class="absolute top-1.5 right-1.5 bg-green-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">BARU</span>
                                <?php endif; ?>
                                <?php if($category->is_popular): ?>
                                    <span class="absolute top-1.5 left-1.5 bg-up-yellow text-white text-[9px] font-bold px-1.5 py-0.5 rounded">🔥 HOT</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-2.5 text-center">
                                <h3 class="text-xs font-semibold text-white group-hover:text-up-yellow transition-colors truncate"><?php echo e($category->name); ?></h3>
                                <?php if($category->publisher): ?>
                                    <p class="text-[10px] text-up-textmuted mt-0.5 truncate"><?php echo e($category->publisher); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="text-center py-16 bg-up-card rounded-xl border border-up-border">
            <i class="fas fa-gamepad text-4xl text-up-textmuted mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">Belum Ada Game</h3>
            <p class="text-up-textmuted text-sm">Kategori game akan segera tersedia.</p>
            <a href="<?php echo e(route('front.index')); ?>" class="inline-flex items-center gap-2 mt-4 text-up-yellow hover:text-up-yellowhover font-semibold text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.front', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\webppobdantopup\resources\views/front/top-up-game.blade.php ENDPATH**/ ?>