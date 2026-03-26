<?php
$f = 'resources/views/layouts/admin.blade.php';
$c = file_get_contents($f);
if (strpos($c, 'admin.users.index') === false) {
    $menu = '
            <a href="{{ route(\'admin.users.index\') }}" class="flex items-center space-x-2 py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs(\'admin.users.*\') ? \'bg-indigo-700\' : \'hover:bg-indigo-600\' }}">
                <i class="fas fa-users w-5 text-center"></i>
                <span>Pengguna</span>
            </a>';
            
    $c = preg_replace('/(\<a href=\"\{\{ route\(\'admin\.banners\.index\'\)\}\}\".*?\<\/a\>)/s', "$1\n$menu", $c);
    file_put_contents($f, $c);
    echo "Menu added";
} else {
    echo "Already contains user menu";
}
