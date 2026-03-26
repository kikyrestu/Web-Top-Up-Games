# P1 Task 6 - Final Organization and WebSite Schema

Tanggal validasi: 2026-03-26

## Objective

Menetapkan schema global `Organization` dan `WebSite` yang konsisten, tidak duplikat, dan punya data kontak yang valid.

## Changes

1. Konsolidasi `Organization` schema menjadi satu sumber data di layout frontend.
2. `Organization` schema sekarang memuat:
- `name`
- `url`
- `logo`
- `contactPoint` (conditional, jika WA/email tersedia)

3. `WebSite` schema sekarang memuat:
- `name`
- `url`
- `potentialAction` (`SearchAction`) ke endpoint search homepage (`?q=`)

4. Menghapus duplikasi script `Organization` yang sebelumnya dirender dua kali.

## Source of Truth

Implementasi schema global berada di:

- `resources/views/layouts/front.blade.php`

## Result

Status task: PASS

Kriteria terpenuhi:

1. Schema global `Organization` tersedia dan tidak duplikat.
2. Schema global `WebSite` tersedia dengan `SearchAction`.
3. Data kontak WA/email terintegrasi ke `contactPoint` bila tersedia.
4. Render schema dilakukan dengan `json_encode` untuk output JSON-LD yang lebih aman.
