# 04. Frontend Route and Page Map

## Public Routes
- /
- /top-up
- /top-up/{gameSlug}
- /ppob
- /ppob/{categorySlug}
- /cek-transaksi
- /promo
- /artikel
- /artikel/{slug}
- /ulasan

## Auth and Account Routes
- /login-otp
- /verify-otp
- /akun
- /akun/transaksi
- /akun/transaksi/{orderCode}
- /akun/profil
- /akun/ulasan

## Checkout Routes
- /checkout/{productSlug}
- /checkout/{productSlug}/payment
- /checkout/{productSlug}/success
- /checkout/{productSlug}/failed

## Admin Routes
- /admin
- /admin/providers
- /admin/providers/health
- /admin/orders
- /admin/payments
- /admin/margins
- /admin/commissions
- /admin/cms
- /admin/seo
- /admin/reviews
- /admin/audit
- /admin/security-events
- /admin/uploads

## Middleware Matrix
- Public pages: cache headers + anti-bot basic.
- Checkout pages: rate limit ketat + CSRF + signed challenge token.
- Account pages: auth + device trust scoring.
- Admin pages: auth + role check + optional IP allowlist + 2FA.
