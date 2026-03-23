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

## Checklist Audit Saat Ini

Status:
- DONE: Sudah siap dipakai.
- PARTIAL: Sudah ada fondasi, perlu dilengkapi.
- TODO: Belum tersedia.

1. Banner dan homepage management: PARTIAL
   - Banner CMS sudah ada, tapi page-composer homepage belum ada.

2. Katalog game/PPOB: DONE
   - Panel admin untuk CRUD category/product/provider sudah tersedia.

3. Paket nominal/top-up item management: DONE
   - Panel admin untuk mapping provider product dan provider price sudah tersedia.

4. Pricing engine: DONE
   - Logic pricing backend sudah terhubung dengan admin pricing rule editor (scope product/category/global).

5. Promo campaign: DONE
   - Engine campaign voucher/cashback sudah tersedia dengan period, quota, dan scope rule.

6. Payment management: DONE
   - Panel admin gateway/metode/fee/priority routing sudah tersedia.

7. Order management: PARTIAL
   - List/detail/reprocess sudah ada, refund/void/dispute workflow belum lengkap.

8. User/customer management: PARTIAL
   - Area akun customer ada, panel admin customer segmentation/lifecycle belum ada.

9. Content pages: DONE
   - CRUD CMS pages sudah tersedia.

10. SEO management: DONE
    - CRUD SEO meta dan mapping entity sudah tersedia.

11. Moderation: DONE
    - Moderasi review single/bulk dan history sudah tersedia.

12. Customer support tools: TODO
    - Ticketing/complaint/SLA admin belum tersedia.

13. Security/risk operations: DONE
    - Security events, audit logs, dan alerts dashboard sudah tersedia.

14. Analytics dashboard: PARTIAL
    - Dashboard operasional sudah ada, analitik bisnis lanjutan belum lengkap.

15. Role and permission: PARTIAL
    - Role dasar ada, permission granular per aksi/menu belum ada.

## Master TODO Eksekusi (Urutan Wajib)

Aturan eksekusi:
- Kerjakan berurutan dari Phase 1 sampai selesai.
- Jangan lompat phase kecuali seluruh item phase sudah DONE.
- Keputusan implementasi mengacu ke dokumen ini.

### Phase 1 - Core Commerce Admin

- [x] Katalog game/PPOB admin (category/product/provider CRUD).
- [x] Paket nominal/top-up item admin.
- [x] Payment management panel (gateway, metode, fee, routing).
- [x] Pricing rule editor (margin, admin fee, fallback rule).

### Phase 2 - Campaign and Operations

- [x] Promo campaign engine (voucher/cashback/rule period).
- [ ] Order operation extension (refund/void/dispute/manual action log).
- [ ] User/customer admin panel (search, segment, status, profile ops).
- [ ] Customer support tools (ticket, complaint, SLA status).

### Phase 3 - Governance and Optimization

- [ ] Permission matrix granular (menu/action scope per role).
- [ ] Dashboard analytics bisnis (funnel, cohort, conversion deep-dive).
- [ ] Homepage composer CMS (manage blok konten storefront dari admin).

## Progress Tracker

Gunakan format ini setiap selesai 1 item agar tracking konsisten:

- Item: <nama item>
- Phase: <1/2/3>
- Status: DONE
- Scope: <fitur yang selesai>
- Bukti: <route/controller/view/migration>
- Catatan: <risk/next dependency>

Update terbaru:

- Item: Katalog game/PPOB admin (category/product/provider CRUD)
- Phase: 1
- Status: DONE
- Scope: CRUD kategori, produk, provider + filtering + active toggle + proteksi hapus relasi
- Bukti: route admin.catalog.* + AdminCatalogController + view admin/catalog/* + sidebar admin
- Catatan: berikutnya lanjut item nominal panel agar pricing input dapat dikelola dari admin

- Item: Paket nominal/top-up item admin
- Phase: 1
- Status: DONE
- Scope: CRUD mapping provider product + CRUD provider price + validasi kombinasi provider-product
- Bukti: route admin.nominal.* + AdminNominalController + view admin/nominal/* + sidebar admin
- Catatan: berikutnya lanjut payment management panel

- Item: Payment management panel
- Phase: 1
- Status: DONE
- Scope: CRUD gateway setting + status active + priority routing + fee + supported methods
- Bukti: route admin.payment.gateways.* + AdminPaymentManagementController + model/migration payment_gateway_settings + view admin/payment/*
- Catatan: resolve gateway checkout kini membaca setting admin aktif, lalu fallback ke config

- Item: Pricing rule editor
- Phase: 1
- Status: DONE
- Scope: CRUD margin rule dengan scope PRODUCT/CATEGORY/GLOBAL + filter mode + active toggle
- Bukti: route admin.pricing.margins.* + AdminPricingController + view admin/pricing/* + relasi Margin product/category
- Catatan: resolve margin checkout kini menjalankan prioritas Product -> Category -> Global

- Item: Promo campaign engine
- Phase: 2
- Status: DONE
- Scope: CRUD promo campaign voucher/cashback + period/quota/scope + apply promo code di checkout + redemption log
- Bukti: route admin.promo.campaigns.* + AdminPromoCampaignController + model/migration promo_campaigns/promo_redemptions + PromoEngineService + integrasi StorefrontController
- Catatan: cashback dicatat sebagai reward metadata order/redemption, sementara voucher mengurangi nominal bayar
