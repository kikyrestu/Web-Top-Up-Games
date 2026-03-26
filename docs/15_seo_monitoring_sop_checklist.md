# P2 Task 13 - Final Monitoring SOP Checklist

Tanggal validasi: 2026-03-26

## Objective

Menetapkan SOP monitoring SEO operasional agar kualitas technical SEO tetap terjaga setelah perubahan fitur.

## Weekly Checklist

1. Search Console
- Cek Coverage: error/indexed/excluded
- Cek Enhancement: schema warnings/errors
- Cek Performance: query, CTR, average position

2. Sitemap and Feed
- Verifikasi `sitemap.xml` dapat diakses
- Verifikasi `feed.xml` dapat diakses
- Pastikan URL noindex tidak muncul di sitemap

3. On-page QA
- Spot check 5 halaman penting: title, description, canonical, robots
- Spot check OG/Twitter preview pada 2 URL acak
- Spot check schema JSON-LD pada 2 URL acak

4. Crawl Hygiene
- Cek broken internal links prioritas tinggi
- Cek redirect chain pada URL utama
- Cek canonical mismatch

## Release Checklist (Before Deploy)

1. Tidak ada diagnostics error pada file SEO yang diubah
2. Perubahan canonical/robots sudah sesuai policy
3. Sitemap generator tidak memasukkan URL noindex
4. Template yang diubah tetap memiliki WebPage schema

## Incident Response

Jika terjadi penurunan indexing/traffic signifikan:

1. Freeze perubahan SEO baru
2. Audit robots, canonical, sitemap dalam 1 jam
3. Rollback perubahan terakhir jika ditemukan regression
4. Catat RCA singkat dan action item preventif

## Result

Status task: PASS

SOP ini menjadi referensi operasional SEO mingguan dan saat release.
