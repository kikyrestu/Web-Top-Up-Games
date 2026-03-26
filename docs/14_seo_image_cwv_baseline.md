# P2 Task 12 - Final Image SEO and CWV Baseline

Tanggal validasi: 2026-03-26

## Objective

Meningkatkan kualitas image SEO dan menurunkan risiko bottleneck Core Web Vitals pada template frontend utama.

## Changes Implemented

1. Menambahkan atribut performa gambar.
- `loading="lazy"` pada gambar non-prioritas.
- `loading="eager"` + `fetchpriority="high"` pada hero image prioritas.
- `decoding="async"` pada gambar utama/listing.

2. Menambahkan/merapikan alt text pada aset visual penting.
- Ikon info bar homepage.
- Thumbnail artikel promo di homepage.
- Elemen logo/flag pada layout.

3. Menjaga prioritas gambar kritikal.
- Banner hero homepage dan featured image artikel detail ditandai prioritas tinggi.

## Baseline CWV (Operational)

Catatan: baseline ini bersifat implementasi teknis (pre-measure), belum pengukuran Lighthouse lapangan.

Target metrik yang dipakai untuk tahap berikutnya:

1. LCP target < 2.5s
2. CLS target < 0.1
3. INP target < 200ms

## Result

Status task: PASS

Kriteria terpenuhi:

1. Template utama sudah menerapkan atribut loading/decoding secara eksplisit.
2. Gambar prioritas tinggi ditetapkan untuk elemen hero.
3. Alt text aset penting sudah tersedia.
