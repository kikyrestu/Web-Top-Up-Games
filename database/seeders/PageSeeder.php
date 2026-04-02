<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Syarat & Ketentuan',
                'slug' => 'syarat-ketentuan',
                'content' => '<h2>1. Pendahuluan</h2><p>Dengan menggunakan layanan kami, Anda menyetujui seluruh aturan yang berlaku...</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Kebijakan Privasi',
                'slug' => 'kebijakan-privasi',
                'content' => '<h2>Pengumpulan Data</h2><p>Kami menjaga privasi data pelanggan kami dan tidak akan menjualnya ke pihak ketiga...</p>',
                'is_active' => true,
            ],
            [
                'title' => 'Daftar Harga Layanan',
                'slug' => 'daftar-harga',
                'content' => '<p>Harga produk diperbarui secara berkala mengikuti harga provider. Untuk daftar harga terlengkap, gunakan fitur pencarian produk di beranda.</p>',
                'is_active' => true,
            ],
            [
                'title' => 'FAQ Top Up dan Pembayaran',
                'slug' => 'faq',
                'content' => '<h3>Berapa lama proses top up?</h3><p>Sebagian besar transaksi diproses otomatis dalam hitungan detik hingga beberapa menit tergantung provider.</p>',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'is_active' => $page['is_active']
                ]
            );
        }
    }
}
