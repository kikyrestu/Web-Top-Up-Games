# 05. Security Risk and Hardening

## Risk Register Ringkas
- Account takeover pada OTP flow.
- Webhook spoofing dari pihak tidak valid.
- Price tampering dari request client.
- Replay attack pada create order.
- Credential leak API provider/PG.
- SQL injection dan mass assignment.
- Abuse bot pada endpoint quote/checkout.
- Session hijacking untuk guest to user sync.

## Hardening Layer

### Identity and Access
- OTP dengan expiry pendek (maks 5 menit) dan retry limit.
- OTP attempt throttling per nomor, per IP, per device.
- Session rotation setelah login berhasil.
- RBAC ketat untuk admin panel.

### API Security
- Server-side recompute price, jangan percaya nilai dari client.
- Wajib Idempotency-Key untuk endpoint order/payment.
- Signature verification untuk semua webhook gateway/provider.
- Allowlist source IP webhook jika disediakan gateway.
- Nonce dan timestamp check untuk mencegah replay.

### Data Security
- Enkripsi secret key menggunakan APP_KEY dan key rotation policy.
- Gunakan PostgreSQL role terpisah (app_rw, app_ro, migration_role).
- Minimalkan data sensitif yang disimpan.
- Audit perubahan tabel kritikal.

### App Security
- Aktifkan Laravel form request validation untuk semua input.
- Gunakan guarded/fillable secara ketat pada model Eloquent.
- Header security: HSTS, CSP, X-Frame-Options, X-Content-Type-Options.
- Sanitasi output konten CMS untuk cegah XSS.

### Infra Security
- WAF rule untuk endpoint kritikal.
- Fail2ban atau equivalent untuk brute-force control.
- Backup harian + restore drill terjadwal.
- Monitoring dan alert untuk error rate, latency, dan fraud spikes.

## Checklist Rilis
- Penetration test basic lulus.
- Secret scanning lulus.
- Dependency vulnerability high/critical = 0.
- Webhook signature test lulus untuk semua gateway/provider.
