<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pulsa Reguler', 'type' => 'ppob', 'icon' => 'fas fa-mobile-alt', 'sort_order' => 1],       
            ['name' => 'Paket Data', 'type' => 'ppob', 'icon' => 'fas fa-wifi', 'sort_order' => 2],
            ['name' => 'Token PLN', 'type' => 'ppob', 'icon' => 'fas fa-bolt', 'sort_order' => 3],
            ['name' => 'Mobile Legends', 'type' => 'game', 'icon' => 'fas fa-gamepad', 'sort_order' => 4],
            ['name' => 'Free Fire', 'type' => 'game', 'icon' => 'fas fa-gamepad', 'sort_order' => 5],
            ['name' => 'PUBG Mobile', 'type' => 'game', 'icon' => 'fas fa-gamepad', 'sort_order' => 6],
            ['name' => 'Valorant', 'type' => 'game', 'icon' => 'fas fa-gamepad', 'sort_order' => 7],
            ['name' => 'Genshin Impact', 'type' => 'game', 'icon' => 'fas fa-gamepad', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert(array_merge($cat, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
