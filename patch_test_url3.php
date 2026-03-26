<?php
$f = 'routes/web.php';
$c = file_get_contents($f);
$c = preg_replace('/if \(!str_contains\(\$request->fullUrl\(\), \'\?=AdminPanel\'\)\) \{.*?return "URL is: " \. \$request->fullUrl\(\);.*?\}/s', "if (!str_contains(\$_SERVER['REQUEST_URI'] ?? '', '?=AdminPanel')) {\n        abort(404);\n    }", $c);
file_put_contents($f, $c);
