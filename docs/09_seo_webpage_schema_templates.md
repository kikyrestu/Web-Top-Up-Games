# P1 Task 7 - Final WebPage Schema Templates

Tanggal validasi: 2026-03-26

## Objective

Menjamin seluruh template frontend memiliki schema `WebPage` yang konsisten sesuai URL canonical masing-masing.

## Coverage Matrix

1. Home (`resources/views/front/index.blade.php`) - WebPage: tersedia
2. Kategori (`resources/views/front/show.blade.php`) - WebPage: tersedia
3. Artikel list (`resources/views/front/article/index.blade.php`) - WebPage: tersedia
4. Artikel detail (`resources/views/front/article/show.blade.php`) - WebPage: tersedia
5. Static page (`resources/views/front/page.blade.php`) - WebPage: ditambahkan
6. Cek pesanan (`resources/views/front/cek-pesanan.blade.php`) - WebPage: ditambahkan
7. Checkout (`resources/views/front/checkout.blade.php`) - WebPage: ditambahkan
8. Invoice (`resources/views/front/invoice.blade.php`) - WebPage: ditambahkan

## Rules Applied

1. Satu schema `WebPage` per template page-level.
2. Nilai `url` schema mengikuti canonical URL halaman.
3. Untuk halaman noindex, schema tetap diperbolehkan sebagai data struktural internal, dengan policy robots tetap noindex.
4. Schema lain (Article, BreadcrumbList, FAQPage) tetap dipertahankan sesuai konteks halaman.

## Result

Status task: PASS

Kriteria terpenuhi:

1. Seluruh template frontend memiliki schema WebPage.
2. Tidak ada error diagnostics di file yang diubah.
3. URL pada schema mengikuti canonical route masing-masing.
