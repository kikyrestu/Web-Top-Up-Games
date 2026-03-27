# 🗺️ Implementation Plan — Web PPOB & Top Up Games

## Overview
Web PPOB & Top Up Games berbasis Laravel + MySQL. Admin bisa konfigurasi API provider dan payment gateway sendiri via panel. Customer bisa transaksi tanpa perlu login (guest checkout).

---

## Phase 1 — Foundation & Setup
**Target: Project siap dikembangkan**

- [x] Init Laravel project di `webppobdantopup/`
- [x] Setup `.env` dengan koneksi MySQL Laragon
- [x] Install dependencies:
  - `laravel/ui` atau `breeze` untuk auth admin
  - `intervention/image` untuk upload gambar
  - `maatwebsite/excel` untuk export Excel
  - `barryvdh/laravel-dompdf` untuk export PDF
  - `guzzlehttp/guzzle` untuk HTTP client ke provider API
- [x] Setup database & jalankan migrasi awal
- [ ] Buat seeder: admin default, kategori default

### Migrasi Database (urutan)
1. `users` — admin & customer
2. `categories` — kategori produk
3. `products` — produk dengan harga modal & jual
4. `api_providers` — konfigurasi provider API
5. `payment_gateways` — konfigurasi payment gateway
6. `transactions` — header transaksi
7. `transaction_items` — detail per transaksi
8. `settings` — key-value config aplikasi
9. `activity_logs` — log sistem & admin

---

## Phase 2 — Admin Panel Core
**Target: Admin bisa kelola produk & konfigurasi**

### 2A. Auth & Layout Admin
- [x] Setup middleware admin
- [x] Layout admin (sidebar, navbar, breadcrumb)
- [x] Halaman login admin
- [x] Dashboard placeholder

### 2B. Manajemen Kategori & Produk
- [x] CRUD kategori
- [x] CRUD produk (dengan harga modal, harga jual, margin)
- [x] Upload gambar produk
- [x] Toggle aktif/nonaktif

### 2C. Konfigurasi API Provider
- [x] CRUD provider
- [x] Form input kredensial dinamis (stored encrypted di DB)
- [ ] Service Layer: `ProviderInterface` + implementasi Digiflazz
- [ ] Test koneksi / ping endpoint
- [ ] Sinkronisasi produk dari provider

### 2D. Konfigurasi Payment Gateway
- [x] CRUD payment gateway
- [x] Input kredensial (encrypted)
- [x] Toggle test/live mode
- [x] Service Layer: `PaymentInterface` + implementasi Tripay

---

## Phase 3 — Transaksi & Proses
**Target: Alur transaksi berjalan end-to-end**

### 3A. Alur Customer
- [x] Halaman home + katalog produk
- [x] Form order (input target + pilih produk)
- [ ] Validasi input (cek nomor/ID jika provider support)
- [x] Pilih payment method
- [x] Preview order + konfirmasi

### 3B. Proses Pembayaran
- [x] Generate invoice / kode unik transaksi
- [x] Redirect ke payment gateway
- [x] Halaman waiting payment + countdown
- [x] Webhook handler payment gateway (update status bayar)

### 3C. Proses ke Provider
- [ ] Setelah payment confirmed → trigger ke API provider
- [ ] Simpan response provider ke DB
- [ ] Update status transaksi (sukses/gagal/pending)
- [ ] Callback/webhook dari provider update status
- [ ] Retry otomatis (maks 3x) jika gagal

### 3D. Notifikasi
- [ ] Notifikasi WhatsApp ke admin jika transaksi gagal
- [ ] Status transaksi real-time di halaman customer

---

## Phase 4 — Admin Panel Lanjutan
**Target: Admin punya kontrol penuh & laporan**

- [ ] Dashboard dengan statistik & grafik (Chart.js)
- [ ] Manajemen transaksi (list, detail, filter, export)
- [ ] Manual retry transaksi
- [ ] Manajemen customer
- [ ] Laporan keuangan (harian/bulanan, export Excel/PDF)
- [ ] Activity log
- [ ] Pengaturan umum (nama toko, logo, maintenance mode)

---

## Phase 5 — Polish & Security
**Target: Siap production**

- [ ] Validasi & sanitasi semua input
- [ ] Rate limiting checkout endpoint
- [ ] Enkripsi semua kredensial API di DB
- [ ] CSRF protection
- [ ] SEO meta tags customer pages
- [ ] Responsive & mobile-friendly
- [ ] Error page custom (404, 500, maintenance)
- [ ] Final testing end-to-end

---

## Tech Decisions

| Kebutuhan | Solusi |
|---|---|
| HTTP Client ke provider | `GuzzleHttp` |
| Queue proses transaksi | Laravel Queue + Database driver |
| Enkripsi kredensial | Laravel `encrypt()` / `Crypt` facade |
| Export Excel | `maatwebsite/excel` |
| Export PDF | `barryvdh/laravel-dompdf` |
| Chart dashboard | `Chart.js` (via CDN) |
| Notif WhatsApp admin | WA API / Fonnte / Telegram Bot |
| Upload gambar | `intervention/image` |

---

## Catatan Penting

> **API Provider yang Direkomendasikan untuk Mulai:**
> - **Digiflazz** — Paling populer di Indonesia, dokumentasi lengkap, support pulsa/data/games/PPOB
> - **Tripay** — Payment gateway lokal, mudah integrasi, banyak metode bayar

> **Urutan Development Prioritas:**
> 1. Setup + Migrasi DB
> 2. Admin Panel (produk & konfigurasi)
> 3. Integrasi 1 Provider + 1 Payment Gateway dulu (Digiflazz + Tripay)
> 4. Alur checkout customer
> 5. Fitur laporan & polish
