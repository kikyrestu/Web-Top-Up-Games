# P1 Task 10 - Final OG and Twitter Image Policy

Tanggal validasi: 2026-03-26

## Objective

Memastikan metadata image untuk OpenGraph dan Twitter konsisten, menggunakan URL absolut, serta memiliki fallback yang stabil.

## Changes

1. Normalisasi URL image metadata di layout frontend.
- Menambahkan resolver `seoImageUrl` agar `meta_image` yang relatif otomatis diubah menjadi URL absolut.

2. Menambahkan metadata image tambahan.
- `og:image:secure_url`
- `og:image:alt`
- `twitter:image:alt`

3. Menjaga fallback image.
- Prioritas fallback: `meta_image` page-level -> logo site -> favicon.

4. Menormalkan override image artikel.
- `meta_image` artikel detail diset ke `asset('storage/...')` agar konsisten absolut.

## Result

Status task: PASS

Kriteria terpenuhi:

1. `og:image` dan `twitter:image` selalu punya URL absolut.
2. Metadata alt image tersedia untuk OG/Twitter.
3. Fallback image tetap aman saat konten tidak punya thumbnail.
