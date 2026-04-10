<?php

namespace App\Console\Commands;

use App\Models\ApiProvider;
use App\Models\Product;
use App\Models\ProductProviderMapping;
use Illuminate\Console\Command;

class MapOkeConnectTagihan extends Command
{
    protected $signature = 'app:map-okeconnect-tagihan';
    protected $description = 'Map OkeConnect products to tagihan categories';

    public function handle()
    {
        $oke = ApiProvider::where('code', 'orderkuota')->first();
        if (!$oke) {
            $this->error('OkeConnect provider not found');
            return;
        }
        $okeId = $oke->id;
        $total = 0;

        // BPJS (cat 292)
        $bpjsMap = [1628 => 'BBPJS', 1625 => 'BBPJSTK', 1623 => 'BBPJSKT'];
        foreach ($bpjsMap as $pid => $code) {
            $total += $this->mapProduct($pid, $okeId, $code, 2000);
        }
        $this->info("BPJS mapped");

        // Internet Pascabayar (cat 223)
        $total += $this->mapProduct(1112, $okeId, 'BYRMYREP', 1450);
        $this->info("Internet mapped");

        // Telepon Pasca Bayar (cat 301)
        $telMap = [
            'INDOSAT' => ['BTELP201', 1300],
            'TELKOMSEL' => ['BTELP205', 1113],
            'TRI' => ['BTELP203', 625],
            'SMARTFREN' => ['BTELP202', 625],
            'XL' => ['BTELP204', 625],
            'TELKOM' => ['BTEL', 1950],
        ];
        $telProducts = Product::where('category_id', 301)->where('is_active', true)->get();
        foreach ($telProducts as $p) {
            foreach ($telMap as $keyword => [$code, $price]) {
                if (str_contains(strtoupper($p->name), $keyword)) {
                    $total += $this->mapProduct($p->id, $okeId, $code, $price);
                    break;
                }
            }
        }
        $this->info("Telepon Pasca mapped");

        // TV Berlangganan (cat 288)
        $tvProducts = Product::where('category_id', 288)->where('is_active', true)->get();
        foreach ($tvProducts as $p) {
            $name = strtoupper($p->name);
            if (str_contains($name, 'MY REPUBLIC')) {
                $total += $this->mapProduct($p->id, $okeId, 'BYRMYREP', 1450);
            } elseif (str_contains($name, 'ICONNET')) {
                $total += $this->mapProduct($p->id, $okeId, 'BYRICON', 1050);
            } elseif (str_contains($name, 'CBN')) {
                $total += $this->mapProduct($p->id, $okeId, 'BYRCBN', 1675);
            } elseif (str_contains($name, 'TRANSVISION') || str_contains($name, 'TELKOMVISION')) {
                $total += $this->mapProduct($p->id, $okeId, 'BTEL', 1950);
            }
        }
        $this->info("TV Berlangganan mapped");

        // Samsat (cat 300)
        $samsatMap = [
            'SULAWESI UTARA' => ['BSAMSULUT', 3000],
            'KALIMANTAN TIMUR' => ['BSAMKALTIM', 2125],
            'JAWA TIMUR' => ['BSAMJATIM', 2125],
            'RIAU' => ['BSAMRIAU', 1900],
            'JAWA TENGAH' => ['BSAMJATENG', 1750],
            'JAWA BARAT' => ['BSAMJABAR', 1600],
            'SULAWESI SELATAN' => ['BSAMSULSEL', 500],
        ];
        $samsatProducts = Product::where('category_id', 300)->where('is_active', true)->get();
        foreach ($samsatProducts as $p) {
            foreach ($samsatMap as $keyword => [$code, $price]) {
                if (str_contains(strtoupper($p->name), $keyword)) {
                    $total += $this->mapProduct($p->id, $okeId, $code, $price);
                    break;
                }
            }
        }
        $this->info("Samsat mapped");

        // Kartu Kredit (cat 296) - only Permata matches
        $kkProducts = Product::where('category_id', 296)->where('is_active', true)->get();
        foreach ($kkProducts as $p) {
            if (str_contains(strtoupper($p->name), 'PERMATA')) {
                $total += $this->mapProduct($p->id, $okeId, 'BKKPRMT', 1550);
            }
        }
        $this->info("Kartu Kredit mapped");

        $this->info("\nTotal new mappings: {$total}");

        // Verify
        $tagihanCats = \App\Models\Category::whereIn('type', ['tagihan', 'pln'])->where('is_active', true)->get();
        foreach ($tagihanCats as $cat) {
            $prodCount = Product::where('category_id', $cat->id)->where('is_active', true)->count();
            $withOke = Product::where('category_id', $cat->id)->where('is_active', true)
                ->whereHas('providerMappings', fn($q) => $q->where('is_active', true)->where('api_provider_id', $okeId))
                ->count();
            $this->line("{$cat->id} | {$cat->name} | products={$prodCount} | okeconnect={$withOke}");
        }
    }

    private function mapProduct(int $productId, int $okeId, string $code, int $price = 0): int
    {
        if (ProductProviderMapping::where('product_id', $productId)->where('api_provider_id', $okeId)->exists()) {
            return 0;
        }
        ProductProviderMapping::create([
            'product_id' => $productId,
            'api_provider_id' => $okeId,
            'provider_product_code' => $code,
            'price_capital' => $price,
            'is_active' => true,
            'priority' => 0,
        ]);
        return 1;
    }
}
