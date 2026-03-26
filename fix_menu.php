<?php
$filepath = __DIR__.'/resources/views/layouts/admin.blade.php';
$content = file_get_contents($filepath);

$content = preg_replace('/<a href="\{\{\s*route\(''admin\.banners\.index''\)\s*\}\}".*?<\/a>\s*/s', '', $content);

$search = '<a href="{{ route(\'admin.articles.index\') }}" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded transition">
                    <i class="fas fa-newspaper w-5"></i>
                    <span x-show="sidebarOpen">Artikel / Berita</span>
                </a>';
$replace = $search . '
                <a href="{{ route(\'admin.banners.index\') }}" class="flex items-center space-x-3 text-gray-300 hover:text-white hover:bg-gray-700 px-3 py-2 rounded transition">
                    <i class="fas fa-images w-5"></i>
                    <span x-show="sidebarOpen">Banner Promo</span>
                </a>';

$content = str_replace($search, $replace, $content);
file_put_contents($filepath, $content);
echo "Done";
