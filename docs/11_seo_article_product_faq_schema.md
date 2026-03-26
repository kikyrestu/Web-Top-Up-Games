# P1 Task 9 - Final Article Product FAQ Schema

Tanggal validasi: 2026-03-26

## Objective

Memfinalkan kualitas schema `Article`, `Product` (melalui `ItemList`), dan `FAQPage` agar lebih lengkap dan konsisten.

## Changes

1. Article schema (`resources/views/front/article/show.blade.php`)
- Menambahkan properti:
  - `url`
  - `inLanguage`
  - `isAccessibleForFree`
- Properti yang sudah ada tetap dipertahankan:
  - `headline`, `datePublished`, `dateModified`, `mainEntityOfPage`, `image`, `author`, `publisher`, `description`.

2. Product schema in ItemList (`resources/views/front/show.blade.php`)
- Menambahkan properti pada item `Product`:
  - `sku`
  - `brand`
- Menambahkan properti pada `Offer`:
  - `url`
  - `itemCondition`
- Properti existing tetap dipertahankan:
  - `priceCurrency`, `price`, `availability`.

3. FAQPage schema (`resources/views/front/page.blade.php`)
- Menambahkan properti:
  - `inLanguage: id-ID`
- Struktur `mainEntity` Q/A tetap dipertahankan.

## Result

Status task: PASS

Kriteria terpenuhi:

1. Article schema memiliki field recommended utama.
2. Product schema memiliki identitas item dan offer yang lebih jelas.
3. FAQ schema memiliki metadata bahasa.
4. Tidak ada diagnostics error pada file yang diubah.
