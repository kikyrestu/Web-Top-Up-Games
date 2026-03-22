# 03. API Worklist Priority

## Priority P0 (Wajib sebelum beta)
- POST /api/v1/auth/otp/request
- POST /api/v1/auth/otp/verify
- POST /api/v1/orders/quote
- POST /api/v1/orders
- GET /api/v1/orders/{orderCode}
- POST /api/v1/payments/webhook/{gateway}
- POST /api/v1/validation/game-id

## Priority P1 (Wajib sebelum launch)
- GET /api/v1/catalog/categories
- GET /api/v1/catalog/products
- GET /api/v1/cms/page/{slug}
- POST /api/v1/reviews
- GET /api/v1/reviews/product/{slug}
- GET /api/v1/account/transactions

## Priority P2 (Optimasi pasca launch)
- GET /api/v1/admin/provider-health
- GET /api/v1/admin/security-events
- GET /api/v1/admin/audit-logs
- POST /api/v1/uploads/scan

## Definition per Endpoint
- Request and response schema wajib terdokumentasi.
- Semua endpoint write wajib punya idempotency atau dedupe key.
- Semua endpoint kritikal punya rate limit policy.
- Semua endpoint order/payment wajib audit event.
