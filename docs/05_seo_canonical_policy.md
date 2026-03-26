# SEO Canonical Policy

Dokumen ini menetapkan aturan canonical URL untuk semua halaman frontend.

## 1) Prinsip Umum

1. Setiap halaman frontend wajib memiliki satu canonical URL final.
2. Canonical harus mengarah ke URL bersih (tanpa parameter query tracking).
3. Halaman noindex tetap memiliki canonical agar sinyal URL konsisten.
4. Jika ada dua URL untuk konten sama, salah satu harus menjadi canonical dan URL lain di-redirect 301.

## 2) Aturan Per Tipe Halaman

1. Home (`/`)
- canonical: `route('front.index')`
- catatan: ketika search query (`q`) aktif, canonical tetap ke home tanpa query.

2. Kategori (`/kategori/{slug}`)
- canonical: `route('front.category', $category->slug ?? $category->id)`
- target final: slug sebagai URL utama.
- catatan: akses berbasis id harus diarahkan 301 ke slug jika slug tersedia.

3. Artikel List (`/artikel`)
- canonical: `route('front.article.index')`

4. Artikel Detail (`/artikel/{slug}`)
- canonical: `route('front.article.show', $article->slug)`

5. Static Page (`/halaman/{slug}`)
- canonical: `route('front.page', $slug)`

6. Cek Pesanan (`/cek-pesanan`)
- canonical: `route('front.cek-pesanan')`
- robots: `noindex,nofollow,noarchive`

7. Checkout (`/checkout`)
- canonical: `route('front.checkout')`
- robots: `noindex,nofollow,noarchive`

8. Invoice (`/transaction/{invoice}`)
- canonical: `route('transaction.show', $transaction->invoice_number)`
- robots: `noindex,nofollow,noarchive`

## 3) Fallback Layout

Jika sebuah view tidak mendefinisikan section canonical, layout memakai fallback:

- `url()->current()`

Fallback ini dipakai sebagai pengaman, bukan pengganti aturan spesifik per halaman.

## 4) Definition of Done Task P0-3

Task `P0 Final canonical policy` selesai jika:

1. Semua halaman frontend utama memiliki canonical eksplisit atau fallback yang sesuai policy.
2. Halaman transaksional noindex memiliki canonical eksplisit.
3. URL kategori non-canonical diarahkan ke canonical slug (301).
4. Dokumen kebijakan canonical tersedia sebagai referensi tim.
