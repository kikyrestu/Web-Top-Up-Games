# P0 Task 4 - Validate Robots and Sitemap Access

Tanggal validasi: 2026-03-25

## Scope

Validasi akses endpoint SEO inti:

1. `robots.txt`
2. `sitemap.xml`
3. `feed.xml`
4. named route terkait (`front.sitemap`, `front.feed`, `transaction.show`)

## Evidence

1. `robots.txt` tersedia di public root dan memuat directive sitemap.
- File: `public/robots.txt`
- Isi:
  - `User-agent: *`
  - `Disallow:`
  - `Sitemap: /sitemap.xml`

2. Route sitemap terdaftar dan mengembalikan XML view.
- Route: `GET /sitemap.xml`
- Name: `front.sitemap`
- Implementasi: closure di `routes/web.php`

3. Route feed terdaftar dan mengembalikan RSS XML.
- Route: `GET /feed.xml`
- Name: `front.feed`
- Implementasi: closure di `routes/web.php`

4. Named route invoice tetap terdaftar (untuk canonical invoice).
- Route: `GET /transaction/{invoice}`
- Name: `transaction.show`

5. Template XML tersedia.
- Sitemap template: `resources/views/sitemap/index.blade.php`
- RSS template: `resources/views/rss/index.blade.php`

## Result

Status task: PASS

Kriteria terpenuhi:

1. Directive robots untuk sitemap tersedia.
2. Endpoint sitemap terdaftar dan punya template output XML.
3. Endpoint feed terdaftar dan punya template output RSS.
4. Named route yang dipakai canonical invoice tersedia.

## Notes

1. Untuk production hardening, directive sitemap bisa ditingkatkan menjadi absolute URL agar lebih eksplisit lintas domain/protocol.
2. Validasi ini fokus pada availability dan wiring endpoint, bukan audit konten URL di dalam sitemap (itu masuk task P0-5).
