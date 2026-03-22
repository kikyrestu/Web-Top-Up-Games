# 01. System Architecture

## Target Arsitektur
Arsitektur API-first modular monolith pada Laravel, siap diekstrak jadi service terpisah jika traffic naik.

## Komponen Utama
- Web Frontend (Blade/Inertia/Vue sesuai fase implementasi).
- API Layer (Laravel route api).
- Domain Modules:
  - Catalog Module
  - Pricing and Routing Module
  - Order and Fulfillment Module
  - Payment Module
  - Auth and Identity Module
  - Review and Testimonial Module
  - CMS and SEO Module
  - Audit and Security Module
- Queue Workers (Redis queue) untuk job async.
- Scheduler (Laravel scheduler) untuk sync produk, cek saldo provider, cleanup log.
- PostgreSQL sebagai source of truth data.
- Redis untuk cache, lock, dan rate-control counter.

## Integrasi Eksternal
- Provider API: Digiflazz, Rajabiller, Orderkuota.
- Payment Gateway: KlikQRISS, Midtrans, Duitku.
- OTP Gateway: WhatsApp/Email provider.

## Pola Komunikasi
- Sinkron: Request API user, validasi ID, create invoice, check status.
- Asinkron:
  - Webhook pembayaran.
  - Order dispatch ke provider.
  - Retry dan failover provider.
  - Audit log enrichment.

## Deployment Baseline
- Nginx + PHP-FPM + Laravel.
- PostgreSQL dengan backup PITR.
- Redis untuk cache/queue.
- Object storage untuk file upload (S3 compatible).
- Worker terpisah dari web process untuk stabilitas.
