# 03. API Contract and Data Models

## API Design Rules
- Versioned API path: /api/v1
- JSON response standard:
  - success: boolean
  - code: string
  - message: string
  - data: object or array
- Idempotency-Key wajib untuk endpoint create order/payment.

## Core Endpoint Draft
- POST /api/v1/auth/otp/request
- POST /api/v1/auth/otp/verify
- POST /api/v1/guest/session/init
- GET /api/v1/catalog/categories
- GET /api/v1/catalog/products
- POST /api/v1/validation/game-id
- POST /api/v1/orders/quote
- POST /api/v1/orders
- GET /api/v1/orders/{orderCode}
- POST /api/v1/payments/webhook/{gateway}
- POST /api/v1/reviews
- GET /api/v1/reviews/product/{slug}
- GET /api/v1/cms/page/{slug}

## Implemented Snapshot (2026-03-23)

### Public and User API
- POST /api/v1/validation/game-id
- POST /api/v1/orders/quote
- POST /api/v1/orders
- GET /api/v1/orders/{orderCode}
- POST /api/v1/payments/initiate
- GET /api/v1/payments/{gatewayReference}/status
- POST /api/v1/payments/webhook/{gateway}
- GET /api/v1/catalog/categories
- GET /api/v1/catalog/products
- GET /api/v1/cms/page/{slug}
- GET /api/v1/reviews/product/{slug}
- POST /api/v1/reviews (auth:sanctum)
- GET /api/v1/account/transactions (auth:sanctum)
- POST /api/v1/uploads/scan (auth:sanctum)

### Admin API
- GET /api/v1/admin/system/readiness
- GET /api/v1/admin/dashboard/overview
- GET /api/v1/admin/dashboard/metrics
- GET /api/v1/admin/dashboard/alerts
- GET /api/v1/admin/dashboard/housekeeping
- GET /api/v1/admin/dashboard/housekeeping/history
- GET /api/v1/admin/dashboard/housekeeping/trend
- GET /api/v1/admin/dashboard/uploads/trend
- GET /api/v1/admin/dashboard/metrics/excel
- GET /api/v1/admin/security-events
- GET /api/v1/admin/audit-logs
- GET /api/v1/admin/audit-logs/export/csv
- POST /api/v1/admin/providers/sync-products
- GET /api/v1/admin/orders/{orderCode}/provider-attempts
- POST /api/v1/admin/orders/{orderCode}/reprocess

### Still Pending from Initial Draft
- POST /api/v1/auth/otp/request
- POST /api/v1/auth/otp/verify
- POST /api/v1/guest/session/init

## Entity and Table Draft (PostgreSQL)
- users
- guest_sessions
- devices
- categories
- products
- provider_products
- provider_prices
- provider_health_checks
- orders
- order_items
- order_provider_attempts
- payments
- payment_webhooks
- commissions
- margins
- reviews
- review_moderations
- cms_pages
- cms_banners
- seo_meta
- audit_logs
- file_upload_logs
- security_events

## Penting: Constraint and Index
- Unique: orders.order_code
- Unique: payments.gateway_reference
- Composite index: provider_prices (product_id, provider_id, updated_at desc)
- Composite index: orders (status, created_at)
- GIN index opsional untuk payload JSONB audit_logs.payload

## Enum Candidate
- order_status: PENDING, PAID, PROCESSING, SUCCESS, FAILED, REFUNDED
- payment_status: UNPAID, PAID, EXPIRED, FAILED
- provider_name: DIGIFLAZZ, RAJABILLER, ORDERKUOTA
- review_status: PENDING_APPROVAL, APPROVED, REJECTED

## Model Responsibility
- PricingEngineService: ranking provider berdasarkan rule game dan multifinance.
- ProviderRouterService: kirim order, retry, failover.
- PaymentWebhookService: verifikasi signature dan trigger fulfillment.
- GuestIdentityService: sinkronisasi guest transaction ke user login.
- AuditLogService: append-only audit event.
