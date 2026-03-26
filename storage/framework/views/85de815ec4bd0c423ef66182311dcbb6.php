<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($global_site_name ?? 'PPOBKu'); ?> Admin - <?php echo $__env->yieldContent('title', 'Control Panel'); ?></title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS with Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        dark: {
                            900: '#0B0F19',
                            800: '#151C2C',
                            700: '#1F293F',
                            600: '#2A3752',
                        },
                        brand: {
                            500: '#4F46E5', // Indigo
                            400: '#6366F1',
                        },
                        accent: {
                            500: '#06B6D4', // Cyan
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar for modern look */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0B0F19; 
        }
        ::-webkit-scrollbar-thumb {
            background: #1F293F; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #2A3752; 
        }
        
        .glass-panel {
            background: rgba(21, 28, 44, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .animate-fade-in-down {
            animation: fadeInDown 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes fadeInDown {
            0% { opacity: 0; transform: translateY(-10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-dark-900 font-sans antialiased text-gray-300 selection:bg-brand-500 selection:text-white" x-data="{ sidebarOpen: true }">

    <!-- Wrapper -->
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0 w-[270px]' : '-translate-x-full w-0'" class="flex-shrink-0 relative z-20 h-full flex flex-col transition-all duration-300 ease-in-out bg-dark-800 border-r border-dark-700 shadow-2xl">
            <!-- Logo Area -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-dark-700 bg-dark-800 shrink-0">
                <div class="flex items-center gap-3 w-full" x-show="sidebarOpen">
                    <?php if(!empty($global_site_logo)): ?>
                        <img src="<?php echo e(asset('storage/' . $global_site_logo)); ?>" alt="<?php echo e($global_site_name ?? 'Logo'); ?>" class="w-9 h-9 rounded-xl object-cover border border-dark-600">
                    <?php else: ?>
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-accent-500 flex items-center justify-center shadow-lg shadow-brand-500/30">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                    <?php endif; ?>
                    <span class="text-xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-300"><?php echo e($global_site_name ?? 'PPOBKu'); ?></span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">
                
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-grid-2 w-5 text-xl transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.dashboard') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-semibold text-sm" x-show="sidebarOpen">Dashboard</span>
                </a>

                <div class="pt-6 pb-2" x-show="sidebarOpen">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-4">Katalog Web</p>
                </div>
                
                <a href="<?php echo e(route('admin.categories.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.categories.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-layer-group w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.categories.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Kategori</span>
                </a>
                
                <a href="<?php echo e(route('admin.products.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.products.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-box-open w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.products.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Produk</span>
                </a>

                <a href="<?php echo e(route('admin.product-sync.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.product-sync.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-sync-alt w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.product-sync.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Sync Produk</span>
                </a>

                <a href="<?php echo e(route('admin.scraped-products.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.scraped-products.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-database w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.scraped-products.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Hasil Scraping</span>
                </a>

                <div class="pt-6 pb-2" x-show="sidebarOpen">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-4">Sales & Customer</p>
                </div>

                <a href="<?php echo e(route('admin.transactions.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.transactions.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group relative">
                    <i class="fas fa-receipt w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.transactions.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Transaksi</span>
                    <!-- badge count -->
                    <span class="absolute right-4 w-2 h-2 rounded-full bg-accent-500 shadow-[0_0_8px_rgba(6,182,212,0.8)]" x-show="sidebarOpen"></span>
                </a>

                <a href="<?php echo e(route('admin.users.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.users.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-users w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.users.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Pelanggan</span>
                </a>

                <div class="pt-6 pb-2" x-show="sidebarOpen">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-4">Main Setting</p>
                </div>

                <a href="<?php echo e(route('admin.api-providers.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.api-providers.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-plug w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.api-providers.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">API Provider</span>
                </a>
                
                <a href="<?php echo e(route('admin.payment-gateways.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.payment-gateways.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-credit-card w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.payment-gateways.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Payment Gateway</span>
                </a>

                <div class="pt-6 pb-2" x-show="sidebarOpen">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest px-4">Konten Website</p>
                </div>

                <a href="<?php echo e(route('admin.banners.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.banners.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-image w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.banners.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Banner Promo</span>
                </a>

                <a href="<?php echo e(route('admin.articles.index')); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.articles.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-newspaper w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.articles.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">CMS Artikel</span>
                </a>

                <a href="<?php echo e(route('admin.settings.index') ?? '#'); ?>" class="flex items-center space-x-3 <?php echo e(request()->routeIs('admin.settings.*') ? 'bg-brand-500/10 text-brand-400' : 'text-gray-400 hover:bg-dark-700/50 hover:text-white'); ?> px-4 py-3 rounded-xl transition duration-200 group">
                    <i class="fas fa-cog w-5 text-lg transition-transform group-hover:scale-110 <?php echo e(request()->routeIs('admin.settings.*') ? 'text-brand-400' : 'text-gray-500 group-hover:text-gray-300'); ?>"></i>
                    <span class="font-medium text-sm" x-show="sidebarOpen">Pengaturan Web</span>
                </a>
                
                <div class="h-6"></div>
            </nav>
            
            <!-- User Profile Section -->
            <div class="p-4 border-t border-dark-700 bg-dark-800 shrink-0" x-show="sidebarOpen">
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-dark-900 border border-dark-700/50 shadow-inner group cursor-pointer hover:border-brand-500/30 transition">
                    <div class="relative">
                        <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->name ?? 'Admin')); ?>&background=4F46E5&color=fff&bold=true" alt="User" class="h-10 w-10 rounded-xl shadow-md border border-dark-600">
                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-green-500 border-2 border-dark-900 rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate"><?php echo e(auth()->user()->name ?? 'Administrator'); ?></p>
                        <p class="text-xs text-brand-400 truncate font-medium">Super Admin</p>
                    </div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>" class="pl-1" @click.stop>
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="p-2 text-gray-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content (Rest of dashboard) -->
        <main class="flex-1 flex flex-col min-w-0 bg-dark-900 relative">
            
            <!-- Navbar Header -->
            <header class="h-20 glass-panel sticky top-0 z-10 w-full flex items-center justify-between px-6 lg:px-10">
                <div class="flex items-center gap-5">
                    <button @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 flex flex-col gap-1.5 items-center justify-center rounded-xl bg-dark-800/80 border border-dark-700 text-gray-400 hover:text-white hover:border-brand-500/50 hover:bg-dark-700 focus:outline-none transition group shadow-sm">
                        <span class="w-4 h-[2px] bg-current rounded-full transition-transform group-hover:w-5"></span>
                        <span class="w-5 h-[2px] bg-current rounded-full"></span>
                        <span class="w-3 h-[2px] bg-current rounded-full transition-transform group-hover:w-5"></span>
                    </button>
                    
                    <div class="hidden md:block">
                        <h1 class="text-xl font-extrabold text-white tracking-tight"><?php echo $__env->yieldContent('header', 'Dashboard'); ?></h1>
                        <div class="flex items-center text-xs font-medium text-gray-500 gap-2 mt-0.5">
                            <span class="hover:text-gray-300 cursor-pointer"><?php echo e($global_site_name ?? 'PPOBKu'); ?> Admin</span> 
                            <i class="fas fa-chevron-right text-[8px] text-gray-600"></i> 
                            <span class="text-brand-400"><?php echo $__env->yieldContent('header', 'Overview'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 sm:gap-5">
                    
                    <!-- Search Bar (Visual) -->
                    <div class="hidden lg:flex items-center relative">
                        <span class="absolute left-4 text-gray-500"><i class="fas fa-search"></i></span>
                        <input type="text" placeholder="Cari menu..." class="w-64 bg-dark-800 border border-dark-700 text-sm text-white rounded-full pl-10 pr-4 py-2 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500/50 transition shadow-inner">
                        <span class="absolute right-4 text-xs font-bold text-gray-500 border border-dark-600 bg-dark-900 rounded px-1.5 py-0.5">⌘ K</span>
                    </div>

                    <div class="w-px h-6 bg-dark-700 hidden sm:block"></div>

                    <!-- View Site -->
                    <a href="/" target="_blank" class="hidden sm:flex items-center gap-2.5 px-4 py-2 bg-dark-800 hover:bg-brand-500/10 border border-dark-700 hover:border-brand-500/50 rounded-full text-sm font-semibold text-gray-300 hover:text-white transition group relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-brand-500/0 via-brand-500/10 to-brand-500/0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                        <i class="fas fa-globe text-brand-400 group-hover:text-accent-500 transition-colors duration-300"></i>
                        <span>Live Site</span>
                    </a>

                    <!-- Notification Bell -->
                    <button class="relative w-10 h-10 flex items-center justify-center rounded-full bg-dark-800 border border-dark-700 text-gray-400 hover:text-white hover:border-gray-600 transition shadow-sm group">
                        <i class="far fa-bell group-hover:animate-swing text-lg"></i>
                        <span class="absolute top-2 right-2.5 w-2.5 h-2.5 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.8)] border-2 border-dark-800"></span>
                    </button>
                    
                </div>
            </header>

            <!-- Main Scrollable Area -->
            <div class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6 lg:p-8">
                
                <!-- Modern Alert Messages -->
                <?php if(session('success')): ?>
                    <div class="mb-6 flex items-center gap-4 bg-brand-500/10 border border-brand-500/20 text-brand-300 px-5 py-4 rounded-xl shadow-lg shadow-brand-500/5 animate-fade-in-down">
                        <div class="w-8 h-8 rounded-full bg-brand-500/20 flex items-center justify-center flex-shrink-0 text-brand-400">
                            <i class="fas fa-check"></i>
                        </div>
                        <p class="text-sm font-semibold text-white tracking-wide"><?php echo e(session('success')); ?></p>
                        <button type="button" class="ml-auto text-gray-500 hover:text-white" onclick="this.parentElement.style.display='none'"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>
                <?php if(session('error')): ?>
                    <div class="mb-6 flex items-center gap-4 bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-4 rounded-xl shadow-lg shadow-red-500/5 animate-fade-in-down">
                        <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0 text-red-400">
                            <i class="fas fa-exclamation"></i>
                        </div>
                        <p class="text-sm font-semibold text-white tracking-wide"><?php echo e(session('error')); ?></p>
                        <button type="button" class="ml-auto text-gray-500 hover:text-white" onclick="this.parentElement.style.display='none'"><i class="fas fa-times"></i></button>
                    </div>
                <?php endif; ?>

                <!-- Dynamic Content View -->
                <div class="text-gray-300">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
                
                <!-- Footer -->
                <footer class="mt-12 pt-6 pb-2 border-t border-dark-800 flex items-center justify-center text-xs font-medium text-gray-500">
                    <p>&copy; <?php echo e(date('Y')); ?> <?php echo e($global_site_name ?? 'PPOBKu'); ?>. All rights reserved.</p>
                </footer>
            </div>
        </main>
    </div>

    <!-- Tailwind Config Animations -->
    <style>
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }
        @keyframes swing {
            20% { transform: rotate(15deg); }
            40% { transform: rotate(-10deg); }
            60% { transform: rotate(5deg); }
            80% { transform: rotate(-5deg); }
            100% { transform: rotate(0deg); }
        }
        .animate-swing {
            animation: swing 1s ease;
            transform-origin: top center;
        }
        /* Dashboard Cards override for nice modern cards */
        .admin-card {
            background-color: #151C2C;
            border: 1px solid #1F293F;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }
    </style>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\PROJECT\webppobdantopup\resources\views/layouts/admin.blade.php ENDPATH**/ ?>