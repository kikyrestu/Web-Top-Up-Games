# 01. Weekly Delivery Plan (Laravel + PostgreSQL)

## Week 1 - Project Foundation
- Setup Laravel baseline, environment config, coding standard.
- Setup PostgreSQL connection, base migrations, seed strategy.
- Setup Redis cache and queue.
- Setup auth scaffolding for OTP flow baseline.
- Setup CI pipeline minimal (lint and build check).

### Deliverables
- Skeleton repository siap development.
- Migration awal untuk users, roles, settings.
- Konfigurasi queue worker and scheduler.

## Week 2 - Catalog and Provider Sync
- Implement category and product domain.
- Implement provider adapter base interface.
- Implement sync job untuk Digiflazz, Rajabiller, Orderkuota.
- Implement provider health check table dan scheduler.

### Deliverables
- Produk dan kategori bisa sync dari provider.
- Admin bisa lihat status sync dan health provider.

## Week 3 - Pricing Engine and Quote
- Implement PricingEngineService.
- Implement rule game top-up: pilih harga modal terendah.
- Implement rule multifinance:
  - Prioritas admin 0.
  - Jika admin sama, pilih komisi tertinggi.
- Implement quote token short TTL dan one-time use.

### Deliverables
- Endpoint quote stabil untuk top-up dan multifinance.
- Logging keputusan routing per quote.

## Week 4 - Order and Payment Integration
- Implement order lifecycle (PENDING to SUCCESS/FAILED).
- Integrasi payment gateway prioritas.
- Implement webhook verification and idempotency.
- Implement provider dispatch + retry + failover.

### Deliverables
- Order berbayar dapat diproses end-to-end.
- Tersedia audit jejak webhook dan dispatch.

## Week 5 - Guest Identity, OTP, and Account Sync
- Implement OTP request and verify.
- Implement guest session and device fingerprint store.
- Implement guest transaction linking saat user login.
- Implement account transaction history API.

### Deliverables
- Guest checkout dan login OTP berjalan.
- Riwayat guest otomatis masuk ke akun user valid.

## Week 6 - Testimonial, CMS, SEO
- Implement testimonial eligibility by SUCCESS transaction.
- Implement review moderation panel.
- Implement CMS banner, article, page blocks.
- Implement SEO meta and sitemap generator.

### Deliverables
- Ulasan terkontrol dan anti-spam flow aktif.
- Konten CMS dan SEO siap produksi.

## Week 7 - Security and Upload Audit
- Implement security events logging and dashboard feed.
- Implement upload validation pipeline + checksum.
- Implement tamper risk scoring untuk checkout.
- Implement challenge escalation flow untuk high-risk request.

### Deliverables
- Risk and hardening controls aktif.
- Upload traceability lengkap.

## Week 8 - Performance, UAT, Launch
- Tuning query, index, and cache.
- UAT scenario lengkap: success, failover, refund path.
- Incident playbook and rollback checklist.
- Production release and post-launch monitoring.

### Deliverables
- Sistem siap go-live dengan observability memadai.
