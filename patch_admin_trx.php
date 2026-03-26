<?php
$filepath = __DIR__.'/resources/views/layouts/admin.blade.php';
$content = file_get_contents($filepath);
$search = '<a href="#" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700
px-3 py-2 rounded transition">
                      <i class="fas fa-shopping-cart w-5"></i>
                      <span x-show="sidebarOpen">Transaksi</span>';
$replace = '<a href="{{ route(\'admin.transactions.index\') }}" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded transition">
                      <i class="fas fa-shopping-cart w-5"></i>
                      <span x-show="sidebarOpen">Transaksi</span>';
$content = str_replace($search, $replace, $content);
file_put_contents($filepath, $content);
echo "Done Transaction Menu";
