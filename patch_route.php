<?php
$f = 'routes/web.php';
$c = file_get_contents($f);
if (strpos($c, 'Route::resource(\'users\',') === false) {
    if (strpos($c, "use App\Http\Controllers\Admin\UserController;") === false) {
        $c = preg_replace('/(use App\\\\Http\\\\Controllers\\\\Admin\\\\TransactionController;)/', "$1\nuse App\\Http\\Controllers\\Admin\\UserController;", $c);
    }
    $c = preg_replace('/(Route::resource\(\'transactions\',.*?;\n)/', "$1    Route::resource('users', UserController::class);\n", $c);
    file_put_contents($f, $c);
    echo "Route added";
} else {
    echo "Already contains user route";
}
