# 📋 Features List — Web PPOB & Top Up Games

## 🖥️ ADMIN PANEL

### 1. Dashboard
- [ ] Ringkasan total transaksi hari ini / bulan ini
- [ ] Total revenue & estimasi profit
- [ ] Statistik transaksi: Sukses / Gagal / Pending
- [ ] Grafik penjualan (chart) per hari/minggu/bulan
- [ ] Notifikasi transaksi terbaru (live update)
- [ ] Widget saldo/status koneksi API provider

### 2. Manajemen Kategori Produk
- [ ] CRUD kategori (nama, ikon, deskripsi, urutan tampil)
- [ ] Aktif / nonaktif kategori
- [ ] Contoh: Games, Pulsa, Paket Data, Listrik, BPJS, dll

### 3. Manajemen Produk
- [ ] CRUD produk (nama, kategori, kode produk provider, deskripsi)
- [ ] Set harga modal (dari provider / HPP)
- [ ] Set harga jual & margin otomatis
- [ ] Aktif / nonaktif produk per item
- [ ] Upload gambar produk
- [ ] Filter & pencarian produk
- [ ] Import produk dari API provider (sync otomatis)

### 4. Konfigurasi API Provider
- [ ] CRUD data provider (nama, logo, keterangan)
- [ ] Input kredensial dinamis (API key, secret, base URL, dll)
- [ ] Test koneksi / ping API provider
- [ ] Set provider aktif per kategori produk
- [ ] Log response API (debug mode)
- [ ] Support multi-provider (Digiflazz, Rajabiller, MTIX, custom)

### 5. Konfigurasi Payment Gateway
- [ ] CRUD payment gateway
- [ ] Input kredensial (merchant ID, API key, secret key, dll)
- [ ] Aktif / nonaktif metode pembayaran
- [ ] Set fee transaksi per metode (nominal / persentase)
- [ ] Test mode / live mode toggle
- [ ] Support: Tripay, Midtrans, Xendit, atau manual transfer

### 6. Manajemen Transaksi
- [ ] List semua transaksi dengan filter (status, tanggal, produk, customer)
- [ ] Detail transaksi (info produk, customer, payment, response API)
- [ ] Manual retry transaksi gagal
- [ ] Manual mark as success / failed (untuk kasus khusus)
- [ ] Export transaksi ke CSV / Excel
- [ ] Log history perubahan status transaksi

### 7. Manajemen User / Customer
- [ ] List semua customer yang pernah transaksi
- [ ] Detail profil & riwayat transaksi per customer
- [ ] Blokir / unblokir customer
- [ ] Reset password customer
- [ ] Tambah admin baru (jika multiple admin)

### 8. Laporan Keuangan
- [ ] Laporan harian / mingguan / bulanan
- [ ] Rekap revenue & profit per produk / kategori
- [ ] Rekap per payment gateway
- [ ] Export laporan ke PDF / Excel

### 9. Pengaturan Umum (Settings)
- [ ] Nama toko, logo, favicon
- [ ] Deskripsi & SEO meta
- [ ] Maintenance mode (on/off + pesan custom)
- [ ] WhatsApp admin untuk notifikasi transaksi
- [ ] Email SMTP konfigurasi
- [ ] Footer teks, sosial media link

### 10. Activity Log
- [ ] Log semua aksi admin (login, edit, hapus, dll)
- [ ] Log webhook payment gateway
- [ ] Log callback API provider

---

## 🛒 HALAMAN CUSTOMER (Frontend)

### 1. Halaman Utama (Home)
- [ ] Banner / hero section
- [ ] Tampilan kategori produk (icon grid)
- [ ] Produk terlaris / featured products
- [ ] Cara pembelian (step by step)
- [ ] Testimoni / review

### 2. Halaman Kategori & Produk
- [ ] List produk per kategori
- [ ] Filter & sort produk
- [ ] Card produk dengan harga, deskripsi

### 3. Halaman Order / Checkout
- [ ] Input target (nomor HP / ID game / nomor pelanggan PLN dll)
- [ ] Pilih nominal/produk
- [ ] Validasi input target (jika provider support)
- [ ] Preview order
- [ ] Pilih metode pembayaran
- [ ] Ringkasan harga (subtotal + fee bayar)

### 4. Halaman Pembayaran
- [ ] Instruksi bayar sesuai metode
- [ ] Countdown waktu pembayaran
- [ ] Redirect ke halaman sukses/gagal

### 5. Halaman Status Transaksi
- [ ] Detail transaksi
- [ ] Status real-time (pending → sukses/gagal)
- [ ] Tombol refresh / cek status
- [ ] Tombol WhatsApp CS jika ada masalah
- [ ] Download bukti transaksi (opsional)

### 6. Halaman Cek Transaksi
- [ ] Cek status transaksi berdasarkan kode unik / nomor HP

---

## 🔧 FITUR TEKNIS

- [ ] Auto-process transaksi setelah payment confirmed (via webhook)
- [ ] Queue system untuk proses transaksi ke provider (Laravel Queue)
- [ ] Retry otomatis jika transaksi gagal (max 3x)
- [ ] Webhook handler payment gateway
- [ ] Callback handler dari provider
- [ ] Enkripsi kredensial API (driver, secret)
- [ ] Rate limiting pada endpoint checkout
- [ ] CSRF protection semua form
- [ ] Maintenance mode
- [ ] Responsive design (mobile-first)
