# BuildyWeb System Planning (Laravel + PostgreSQL)

Dokumentasi ini adalah blueprint awal pengembangan sistem PPOB dan Top-Up Games versi production-ready.

## Daftar Dokumen
- 01-system-architecture.md
- 02-business-flow.md
- 03-api-contract-and-models.md
- 04-frontend-route-and-page-map.md
- 05-security-risk-and-hardening.md
- 06-upload-audit-and-traceability.md
- 07-tamper-extension-filtering.md
- 08-development-roadmap.md

## Daftar Dokumen Implementasi
- implementation/01-weekly-delivery-plan.md
- implementation/02-module-task-breakdown.md
- implementation/03-api-worklist-priority.md
- implementation/04-frontend-execution-plan.md

## Prinsip Utama
- Laravel sebagai core framework backend (API-First).
- PostgreSQL sebagai primary database dengan indexing ketat.
- Event-driven processing untuk pembayaran, provider order, dan audit.
- Security by design untuk guest mode, login OTP, dan transaksi finansial.

## Notasi Lingkungan
- Local: pengembangan harian.
- Staging: uji integrasi API provider/PG.
- Production: deployment live dengan hardening penuh.
