# SEO Title & Meta Description Rules

Dokumen ini menjadi acuan tetap untuk metadata SEO di halaman frontend.

## 1) Global Fallback

Global fallback ditangani di layout frontend:

- title fallback halaman: `Beranda`
- site suffix: ` - {site_name}`
- meta description fallback: `site_description` dari settings

Formula title final:

- jika `@section('title')` ada: `{page_title} - {site_name}`
- jika kosong: `{site_name}`

Formula description final:

- jika `@section('meta_description')` ada: pakai nilai section
- jika kosong: pakai `site_description`

## 2) Rule Per Tipe Halaman

1. Home (`/`)
- title: `Top Up Game Cepat & Murah`
- description: menjelaskan nilai utama top up + PPOB
- catatan: jika ada query search (`q`), robots harus `noindex,follow`

2. Kategori (`/kategori/{slug}`)
- title: `{Nama Kategori} Top Up Murah`
- description: menjelaskan top up kategori + metode pembayaran + kecepatan proses

3. Artikel List (`/artikel`)
- title: `Artikel, Promo, dan Berita Game`
- description: ringkas tentang konten artikel/promo

4. Artikel Detail (`/artikel/{slug}`)
- title: `{Judul Artikel}`
- description: ringkasan isi artikel maksimal 160 karakter

5. Static Page (`/halaman/{slug}`)
- title: dari konfigurasi data halaman
- description: dari konfigurasi data halaman

6. Cek Pesanan (`/cek-pesanan`)
- title: `Cek Status Pesanan`
- description: fungsi halaman tracking pesanan
- catatan: harus `noindex,nofollow,noarchive`

7. Checkout (`/checkout`)
- title: `Checkout Pesanan`
- description: fungsi konfirmasi pembayaran
- catatan: harus `noindex,nofollow,noarchive`

8. Invoice (`/transaction/{invoice}`)
- title: `Invoice Transaksi`
- description: detail status pembayaran transaksi
- catatan: harus `noindex,nofollow,noarchive`

## 3) Batasan Copy

Agar konsisten dan aman untuk snippet mesin pencari:

- panjang title target: 45-65 karakter
- panjang meta description target: 120-160 karakter
- hindari duplikasi title antar halaman utama
- hindari keyword stuffing
- gunakan bahasa natural dan intent-driven

## 4) Definition of Done Task P0-2

Task `P0 Final title description rules` dianggap selesai bila:

1. Semua view frontend punya `@section('title')`
2. Semua view frontend punya `@section('meta_description')`
3. Fallback global aktif di layout frontend
4. Halaman pencarian internal dan transaksi tetap noindex sesuai policy
5. Dokumen rule ini tersimpan sebagai referensi implementasi berikutnya
