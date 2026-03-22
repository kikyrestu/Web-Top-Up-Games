# 07. Tamper Extension Filtering

## Tujuan
Mendeteksi dan meminimalkan manipulasi request oleh browser extension, userscript, atau intercept tool pada proses checkout.

## Threat Pattern
- Inject JavaScript yang mengubah nominal atau product code di browser.
- Intercept request kemudian edit payload price/admin/fee.
- Replay request valid berkali-kali.
- Forge success callback di sisi client.

## Strategy Layer

### Layer 1: Client Signal
- Generate device fingerprint non-invasif.
- Rekam signal integritas halaman (hash resource kritikal, timing anomalies).
- Captcha/challenge adaptif jika score risiko naik.

### Layer 2: Server Validation (Wajib)
- Abaikan semua nominal dari client, hitung ulang di server.
- Product price dan admin fee hanya dari tabel internal/cache provider.
- Validasi signature internal pada quote token.
- Quote token TTL pendek (misal 60-120 detik) dan one-time use.

### Layer 3: Risk Scoring
- Risk score dari kombinasi:
  - device mismatch
  - perubahan user-agent mendadak
  - frekuensi request abnormal
  - banyak quote tanpa bayar
  - pola payload anomali
- Jika score tinggi: blok sementara, step-up challenge, atau require login.

### Layer 4: Forensic Logging
- Simpan request fingerprint, header subset, checksum payload, dan decision reason.
- Correlate dengan security_events dan order_provider_attempts untuk investigasi.

## Decision Matrix
- Low risk: allow.
- Medium risk: allow dengan challenge tambahan.
- High risk: reject dan log event SECURITY_BLOCKED.

## Catatan Penting
Filtering extension hanya lapisan tambahan. Keamanan utama tetap server-authoritative logic untuk harga, status pembayaran, dan fulfillment.
