<?php
$filepath = __DIR__.'/resources/views/layouts/admin.blade.php';
$content = file_get_contents($filepath);
$search = '<a href="{{ route(\'admin.payment-gateways.index\') }}"';
$replace = '<a href="{{ route(\'admin.articles.index\') }}" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded transition">
                    <i class="fas fa-newspaper w-5"></i>
                    <span x-show="sidebarOpen">Artikel / Berita</span>
                </a>
                '.$search;
$content = str_replace($search, $replace, $content);

// While we are at it, replace settings route too
$search2 = '<a href="#" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700
px-3 py-2 rounded transition">
                      <i class="fas fa-cog w-5"></i>
                      <span x-show="sidebarOpen">Pengaturan</span>';
$replace2 = '<a href="{{ route(\'admin.settings.index\') }}" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded transition">
                      <i class="fas fa-cog w-5"></i>
                      <span x-show="sidebarOpen">Pengaturan</span>';

$content = str_replace($search2, $replace2, $content);
file_put_contents($filepath, $content);
echo "Done";
