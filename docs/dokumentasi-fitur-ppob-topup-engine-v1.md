# Dokumentasi Fitur Source Code: PPOB & Top-Up Engine v1.0


## 1. Core Engine: Smart Price Aggregator (3 Providers)
Ini adalah jantung dari sistem yang mengotomatisasi pencarian modal termurah secara real-time.

- **Triple-Provider Integration**: Koneksi API native ke Digiflazz, Rajabiller, dan Orderkuota.
- **Auto-Comparison Logic**: Sistem secara otomatis membandingkan harga produk yang sama dari ketiga provider tersebut dan mengambil harga terendah sebagai basis modal.
	- **Khusus PPOB Multifinance:**
		- Jika admin sama, sistem otomatis memilih provider dengan komisi/keuntungan tertinggi.
		- Jika ada provider dengan admin 0 rupiah, sistem otomatis memilih provider tersebut meskipun komisinya bukan yang tertinggi.
		- Logika ini berlaku untuk perbandingan ketiga provider (Digiflazz, Rajabiller, Orderkuota).
- **Dynamic Profit Margin**: Input manual nominal komisi/keuntungan per kategori atau per produk melalui dashboard (bisa pilih Flat atau Percentage).
- **Provider Failover System**: Jika salah satu provider (misal: Digiflazz) sedang maintenance atau stok kosong, sistem secara otomatis mengalihkan pesanan ke provider termurah berikutnya (Rajabiller atau Orderkuota).
- **Automatic Product Sync**: Fitur untuk sinkronisasi otomatis nama produk, kategori, dan harga modal agar tetap up-to-date dengan provider.

## 2. Advanced SEO Management (High Visibility)
Fitur ini memastikan website klien mudah dirayapi mesin pencari dan memiliki performa organik yang bagus.

- **Dynamic Meta Tags**: Pengaturan Meta Title, Description, dan Keywords yang berbeda untuk setiap halaman produk, kategori, dan artikel.
- **Open Graph (OG) Tags**: Optimasi tampilan saat link website dibagikan ke media sosial (WhatsApp, Facebook, X), lengkap dengan thumbnail otomatis.
- **Custom URL Slugs**: URL yang ramah SEO (contoh: `domain.com/top-up/mobile-legends` bukan `domain.com/p?id=123`).
- **Sitemap & Robots.txt Generator**: Otomatis menghasilkan file sitemap untuk mempercepat indeksasi oleh Google Search Console.
- **Image Alt Optimizer**: Otomatis menambahkan tag alt pada setiap gambar produk game untuk optimasi pencarian gambar.

## 3. Full Dynamic CMS & Content Manager
Memungkinkan klien mengelola tampilan visual tanpa menyentuh kode program.

- **Hero Section & Slider**: Atur banner utama, teks tagline, dan tombol call-to-action langsung dari dashboard.
- **Promo Ads Management**: Slot iklan popup atau sticky banner untuk info diskon atau event terbatas.
- **Dynamic Navigation & Footer**: Sistem Drag & Drop untuk menyusun menu navigasi atas dan tautan di bagian bawah website.
- **Article/Blog Module**: Editor teks kaya (WYSIWYG) untuk membuat artikel tutorial, berita game, atau info promo guna mendongkrak trafik SEO.

## 4. Payment & Transaction Automated System
Integrasi pembayaran yang aman dan serba otomatis.

- **API PG Configuration**: Dashboard untuk memasukkan API Key, Client Secret, dan Merchant ID dari Payment Gateway (seperti Midtrans, Duitku, atau Tripay).
- **Automated Webhook**: Sistem penanganan notifikasi pembayaran sukses yang akan langsung memicu pengiriman produk ke user secara instan (24 jam nonstop).
- **Order Tracking & History**: Halaman riwayat transaksi bagi user untuk mengecek status pesanan mereka (Pending, Success, atau Failed).
- **In-App ID Validator**: Fitur pengecekan ID Game dan Server (seperti Nickname MLBB atau Free Fire) untuk memastikan user tidak salah memasukkan ID sebelum membayar.

## 5. Admin Security & Monitoring Dashboard
- **Provider Balance Monitor**: Cek sisa saldo di Digiflazz, Rajabiller, dan Orderkuota langsung dari satu halaman admin.
- **Transaction Logs**: Rekam jejak semua request API baik ke Payment Gateway maupun ke Provider untuk kebutuhan audit jika terjadi eror.
- **Secure API Storage**: Enkripsi kunci API di database untuk mencegah pencurian data sensitif.
