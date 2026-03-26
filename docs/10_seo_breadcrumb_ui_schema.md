# P1 Task 8 - Final Breadcrumb UI and Schema

Tanggal validasi: 2026-03-26

## Objective

Menyamakan breadcrumb visual di halaman dengan `BreadcrumbList` schema agar tidak terjadi mismatch label/struktur.

## Changes

1. Artikel detail (`resources/views/front/article/show.blade.php`)
- Mengubah label breadcrumb UI:
  - `Home` -> `Beranda`
  - `Berita` -> `Artikel`
- Tujuan: sinkron dengan `BreadcrumbList` schema yang sudah memakai `Beranda > Artikel > {judul}`.

2. Kategori (`resources/views/front/show.blade.php`)
- Menambahkan breadcrumb visual UI:
  - `Beranda > {Nama Kategori}`
- Tujuan: sinkron dengan `BreadcrumbList` schema yang sudah ada di halaman kategori.

3. Static page (`resources/views/front/page.blade.php`)
- Sudah sinkron sebelumnya (`Beranda > {judul halaman}`), tidak perlu perubahan.

## Result

Status task: PASS

Kriteria terpenuhi:

1. Halaman dengan `BreadcrumbList` schema memiliki breadcrumb visual yang sepadan.
2. Label breadcrumb UI sesuai dengan label schema pada halaman yang sama.
3. Tidak ada diagnostics error setelah perubahan.
