<?php $__env->startSection('title', 'Edit Produk'); ?>
<?php $__env->startSection('header', 'Edit Produk'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex justify-center w-full"><div class="w-full max-w-3xl"><div class="glass-panel rounded-2xl shadow-xl border border-dark-700 p-6 sm:p-8 mb-8 relative overflow-hidden w-full">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mb-6 border-l-4 border-brand-500 pl-3">
        <h2 class="text-lg font-bold text-white tracking-tight">Edit Data Produk</h2>
        <p class="text-xs text-gray-500 mt-1">Ubah informasi produk di bawah ini sesuai kebutuhan.</p>
    </div>

    <form action="<?php echo e(route('admin.products.update', $product->id)); ?>" method="POST" enctype="multipart/form-data" class="relative z-10">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="name" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Produk <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-10 justify-center pointer-events-none">
                        <i class="fas fa-box text-gray-500"></i>
                    </div>
                    <input type="text" name="name" id="name" value="<?php echo e(old('name', $product->name)); ?>" class="w-full pl-10 pr-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner" required>
                </div>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1.5"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div>
                <label for="category_id" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kategori <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-10 justify-center pointer-events-none">
                        <i class="fas fa-layer-group text-gray-500"></i>
                    </div>
                    <select name="category_id" id="category_id" class="w-full pl-10 pr-10 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner appearance-none" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id', $product->category_id) == $category->id ? 'selected' : ''); ?>><?php echo e($category->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1.5"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <?php
            $existingMappings = $product->providerMappings
                ->map(function ($mapping) {
                    return [
                        'api_provider_id' => $mapping->api_provider_id,
                        'provider_product_code' => $mapping->provider_product_code,
                        'price_capital' => $mapping->price_capital,
                        'priority' => $mapping->priority,
                    ];
                })
                ->values()
                ->all();

            $defaultMappings = array_pad($existingMappings, 3, ['api_provider_id' => '', 'provider_product_code' => '', 'price_capital' => '', 'priority' => '']);
            $oldMappings = old('provider_mappings', $defaultMappings);
        ?>
        <div class="mb-5 bg-dark-800/50 p-4 rounded-xl border border-dark-700">
            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-3">Mapping Provider (Multi Provider)</label>
            <div class="space-y-3">
                <?php $__currentLoopData = $oldMappings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $mapping): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-dark-900/70 border border-dark-700 rounded-xl p-3">
                        <div class="md:col-span-4">
                            <label class="block text-[11px] text-gray-500 mb-1">Provider</label>
                            <select name="provider_mappings[<?php echo e($index); ?>][api_provider_id]" class="w-full bg-dark-800 border border-dark-600 text-white text-sm rounded-lg p-2.5">
                                <option value="">-- Pilih Provider --</option>
                                <?php $__currentLoopData = $providers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $provider): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($provider->id); ?>" <?php echo e((string) ($mapping['api_provider_id'] ?? '') === (string) $provider->id ? 'selected' : ''); ?>><?php echo e($provider->name); ?> (<?php echo e(strtoupper($provider->code)); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] text-gray-500 mb-1">Kode Produk Provider</label>
                            <input type="text" name="provider_mappings[<?php echo e($index); ?>][provider_product_code]" value="<?php echo e($mapping['provider_product_code'] ?? ''); ?>" class="w-full bg-dark-800 border border-dark-600 text-white rounded-lg p-2.5 font-mono text-sm" placeholder="contoh: TSEL10">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] text-gray-500 mb-1">Modal Provider</label>
                            <input type="number" step="0.01" min="0" name="provider_mappings[<?php echo e($index); ?>][price_capital]" value="<?php echo e($mapping['price_capital'] ?? ''); ?>" class="w-full bg-dark-800 border border-dark-600 text-white rounded-lg p-2.5 text-sm" placeholder="0">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] text-gray-500 mb-1">Prioritas</label>
                            <input type="number" min="0" max="999" name="provider_mappings[<?php echo e($index); ?>][priority]" value="<?php echo e($mapping['priority'] ?? $index); ?>" class="w-full bg-dark-800 border border-dark-600 text-white rounded-lg p-2.5 text-sm" placeholder="<?php echo e($index); ?>">
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="price_capital" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Harga Modal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-12 justify-center pointer-events-none border-r border-dark-600">
                        <span class="text-gray-500 font-bold text-sm">Rp</span>
                    </div>
                    <input type="number" name="price_capital" id="price_capital" value="<?php echo e(old('price_capital', $product->price_capital)); ?>" class="w-full pl-14 pr-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner font-mono" required>
                </div>
            </div>

            <div>
                <label for="price_sell" class="block text-brand-400 text-xs font-bold uppercase tracking-wider mb-2">Harga Jual <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-12 justify-center pointer-events-none border-r border-brand-500/30">
                        <span class="text-brand-400 font-bold text-sm">Rp</span>
                    </div>
                    <input type="number" name="price_sell" id="price_sell" value="<?php echo e(old('price_sell', $product->price_sell)); ?>" class="w-full pl-14 pr-4 py-2.5 bg-brand-500/5 border border-brand-500/30 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner font-mono ring-1 ring-brand-500/10" required>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <label for="description" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Deskripsi Produk</label>
            <textarea name="description" id="description" rows="3" class="w-full p-3 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner"><?php echo e(old('description', $product->description)); ?></textarea>
        </div>

        <div class="mb-6 bg-dark-800/30 p-5 rounded-xl border border-dark-700 flex flex-col gap-6">
            <div>
                <label for="image" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Gambar / Icon Produk</label>
                <div class="flex items-center gap-4">
                    <?php if($product->image): ?>
                        <div class="relative group h-16 w-16 shrink-0">
                            <img src="<?php echo e(asset('storage/' . $product->image)); ?>" class="h-16 w-16 object-cover rounded-xl border border-dark-600 shadow-md transition-transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <i class="fas fa-image text-white"></i>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="w-16 h-16 shrink-0 rounded-xl bg-dark-700 border border-dark-600 shadow-inner flex items-center justify-center text-gray-500">
                            <i class="fas fa-image text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="flex-1">
                        <p class="text-[11px] text-gray-500 mb-2">Upload file baru untuk mengganti gambar saat ini. Biarkan kosong jika tidak ingin mengubah.</p>
                        <input type="file" name="image" id="image" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:transition-colors cursor-pointer" accept="image/*">
                    </div>
                </div>
                <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="pt-4 border-t border-dark-700/50 flex justify-between items-center">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" <?php echo e(old('is_active', $product->is_active) ? 'checked' : ''); ?>>
                    <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-dark-600"></div>
                    <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Produk Aktif</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-dark-700">
            <a href="<?php echo e(route('admin.products.index')); ?>" class="px-5 py-2.5 bg-dark-800 rounded-xl text-sm font-bold text-gray-400 hover:text-white hover:bg-dark-700 border border-dark-600 hover:border-gray-500 transition-all shadow-sm">Batal</a>
            <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 hover:from-brand-400 hover:to-brand-500 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\webppobdantopup\resources\views/admin/products/edit.blade.php ENDPATH**/ ?>