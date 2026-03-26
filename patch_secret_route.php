<?php
$f = 'routes/web.php';
$c = file_get_contents($f);
if (strpos($c, '/admin/buildywebadmin/Login') === false) {
    $code = <<<'EOD'

// Secret Admin Login
Route::get('/admin/buildywebadmin/Login', function (Request $request) {
    if (!str_contains($request->fullUrl(), '?=AdminPanel')) {
        abort(404);
    }
    return view('admin.auth.login');
})->middleware('guest')->name('admin.secret.login');

EOD;
    $c = preg_replace('/(\/\/ --- ADMIN \/ MEMBER DASHBOARD ---)/', $code . "\n$1", $c);
    file_put_contents($f, $c);
    echo "Secret route added.";
} else {
    echo "Secret route exists.";
}
