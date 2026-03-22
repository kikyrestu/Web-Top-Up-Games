# 04. Frontend Execution Plan

## Stack Direction
- Laravel Blade plus progressive enhancement (fase awal).
- Opsi Inertia/Vue untuk panel admin kompleks (fase lanjut).

## Public Experience
- Home, top-up, ppob, artikel, promo.
- Product detail plus order form dengan validasi realtime.
- Checkout cepat untuk guest dengan OTP optional.

## Account Experience
- Login OTP flow.
- Riwayat transaksi (pagination + filter status).
- Form ulasan produk eligible.

## Admin Experience
- Dashboard KPI order, payment, provider health.
- Margin and commission settings.
- CMS and SEO manager.
- Review moderation and audit monitor.

## Frontend Security Controls
- CSRF token untuk form write.
- Nonce CSP untuk script inline.
- Jangan expose key sensitif di client.
- Semua nominal dan status final tetap berasal dari server.

## Frontend Done Criteria
- Lighthouse mobile minimal 85 untuk halaman utama.
- Core page TTFB konsisten dalam batas SLA internal.
- Semua flow kritikal punya fallback state dan error message jelas.
