<?php
$filepath = __DIR__.'/resources/views/front/index.blade.php';
$content = file_get_contents($filepath);
$search = "this.products = [
                        { id: 1, name: 'Pulsa 5.000', price: 5500 },
                        { id: 2, name: 'Pulsa 10.000', price: 10500 },
                        { id: 3, name: 'Pulsa 20.000', price: 20000 },
                        { id: 4, name: 'Pulsa 50.000', price: 49500 },
                        { id: 5, name: 'Pulsa 100.000', price: 99000 }
                    ];";
$replace = "fetch(`/api/ppob/products?category=\${this.tab}&provider=\${this.provider.name}`)
                        .then(res => res.json())
                        .then(data => this.products = data);";
$content = str_replace($search, $replace, $content);
file_put_contents($filepath, $content);
echo "Widget patched!";
