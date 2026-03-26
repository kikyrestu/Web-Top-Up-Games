<?php
$f = 'routes/web.php';
$c = file_get_contents($f);
$c = str_replace('abort(404);', 'return "URL is: " . $request->fullUrl();', $c);
file_put_contents($f, $c);
