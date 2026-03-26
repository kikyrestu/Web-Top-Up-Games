# P0 Task 5 - Sync Sitemap With Indexable URLs Only

Tanggal validasi: 2026-03-25

## Objective

Memastikan sitemap hanya berisi URL yang boleh diindeks mesin pencari.

## Input Policy

### URL indexable

1. Home (`/`)
2. Kategori (`/kategori/{slug|id}`)
3. Artikel list (`/artikel`)
4. Artikel detail (`/artikel/{slug}`)
5. Static page indexable (`/halaman/{slug}`) untuk slug:
- `daftar-harga`
- `faq`
- `kontak`
- `syarat-ketentuan`
- `kebijakan-privasi`

### URL noindex (tidak boleh masuk sitemap)

1. `/cek-pesanan`
2. `/checkout`
3. `/transaction/{invoice}`
4. URL pencarian internal `/?q=...`

## Implementation

Sitemap generator di `routes/web.php` diperkuat dengan:

1. `noindexUrls` list untuk URL noindex statis.
2. Deduplikasi URL dengan `unique('loc')`.
3. Filter eksklusi URL noindex sebelum render XML.

## Result

Status task: PASS

Kriteria terpenuhi:

1. URL noindex tidak diproduksi ke sitemap.
2. URL sitemap unik (tanpa duplikasi `loc`).
3. URL indexable utama tetap tercakup.

## File Affected

1. `routes/web.php`
2. `resources/views/sitemap/index.blade.php` (template tetap, tidak diubah)
