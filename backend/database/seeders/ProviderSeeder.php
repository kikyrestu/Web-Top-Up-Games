<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['code' => 'DIGIFLAZZ', 'name' => 'Digiflazz'],
            ['code' => 'RAJABILLER', 'name' => 'Rajabiller'],
            ['code' => 'ORDERKUOTA', 'name' => 'Orderkuota'],
        ];

        foreach ($providers as $provider) {
            Provider::query()->updateOrCreate(
                ['code' => $provider['code']],
                [
                    'name' => $provider['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}
