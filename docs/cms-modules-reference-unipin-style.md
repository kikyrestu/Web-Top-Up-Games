# CMS Modules Reference (Unipin-Style)

Dokumen ini merangkum modul CMS dan operasional yang umum dipakai pada platform top up game/PPOB dengan pola seperti Unipin.

## Daftar Modul Utama

1. Banner dan homepage management
   - Hero banner, promo slot, urutan tampilan, periode tayang.

2. Katalog game/PPOB
   - Kategori, produk, status aktif/nonaktif, urutan katalog.

3. Paket nominal/top-up item management
   - Daftar denominasi, label produk, availability per item.

4. Pricing engine
   - Harga dasar, margin, admin fee, promo price, aturan fallback.

5. Promo campaign
   - Voucher, cashback, event period, syarat dan ketentuan promo.

6. Payment management
   - Gateway, metode pembayaran, fallback gateway, fee per channel.

7. Order management
   - Monitoring status, retry/reprocess, refund, manual override operasional.

8. User/customer management
   - Profil customer, riwayat transaksi, segmentasi pengguna.

9. Content pages
   - FAQ, artikel, terms, privacy policy, help center.

10. SEO management
    - Meta title/description, OG tags, slug, sitemap/robots.

11. Moderation
    - Moderasi review/rating dan konten UGC terkait.

12. Customer support tools
    - Ticketing, komplain, status penanganan dan catatan CS.

13. Security/risk operations
    - Fraud flagging, rate limiting, security event monitoring, audit log.

14. Analytics dashboard
    - Sales, conversion, payment success rate, provider health metrics.

15. Role and permission
    - Role-based access (admin, editor, ops, finance) dan pembatasan aksi.

## Minimum Priority untuk Implementasi

Untuk mencapai level produksi yang stabil, prioritas minimum:

1. Katalog + pricing + promo.
2. Payment + order operations.
3. CMS pages + SEO.
4. Dashboard + audit/security.

## Catatan

- Ini adalah referensi modul produk, bukan cloning UI atau implementasi spesifik pihak lain.
- Detail data model dan endpoint tiap modul bisa diturunkan ke dokumen lanjutan setelah finalisasi scope.
