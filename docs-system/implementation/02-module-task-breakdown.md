# 02. Module Task Breakdown

## A. Auth and Identity Module
### Migration
- users, otp_requests, guest_sessions, devices.

### Service
- OtpService
- GuestIdentityService
- DeviceTrustService

### Controller/API
- AuthOtpController
- GuestSessionController
- AccountHistoryController

### Job/Event
- CleanupExpiredOtpJob
- LinkGuestTransactionsJob

### Tests
- OTP rate-limit test.
- OTP expiry test.
- Guest-to-user sync consistency test.

## B. Catalog and Provider Module
### Migration
- categories, products, provider_products, provider_prices, provider_health_checks.

### Service
- ProviderAdapterInterface
- DigiflazzAdapter
- RajabillerAdapter
- OrderkuotaAdapter
- ProductSyncService

### Controller/API
- CatalogController
- ProviderHealthController

### Job/Event
- SyncProviderProductsJob
- UpdateProviderHealthJob

### Tests
- Mapping product field antar provider.
- Fallback saat salah satu provider timeout.

## C. Pricing and Routing Module
### Migration
- commissions, margins, pricing_logs.

### Service
- PricingEngineService
- ProviderRouterService
- QuoteTokenService

### Controller/API
- QuoteController
- PricingAdminController

### Job/Event
- WarmPriceCacheJob

### Tests
- Top-up lowest-price selection test.
- Multifinance admin-zero priority test.
- Multifinance highest-commission tie-break test.

## D. Order and Fulfillment Module
### Migration
- orders, order_items, order_provider_attempts.

### Service
- OrderService
- FulfillmentService

### Controller/API
- OrderController
- OrderStatusController

### Job/Event
- DispatchOrderToProviderJob
- RetryProviderAttemptJob

### Tests
- Idempotent order creation test.
- Failover chain test.

## E. Payment Module
### Migration
- payments, payment_webhooks, refunds.

### Service
- PaymentGatewayService
- WebhookVerificationService
- PaymentStateMachineService

### Controller/API
- PaymentController
- PaymentWebhookController

### Job/Event
- ProcessPaidOrderJob
- ReconcilePaymentJob

### Tests
- Webhook signature verification test.
- Duplicate webhook idempotency test.

## F. Review and Testimonial Module
### Migration
- reviews, review_moderations.

### Service
- ReviewEligibilityService
- ReviewModerationService

### Controller/API
- ReviewController
- AdminReviewController

### Job/Event
- NotifyReviewApprovedJob

### Tests
- Verified purchase lock test.
- Guest historical purchase review eligibility test.

## G. CMS and SEO Module
### Migration
- cms_pages, cms_banners, seo_meta.

### Service
- CmsPageService
- SeoMetaService
- SitemapService

### Controller/API
- CmsController
- SeoController

### Job/Event
- GenerateSitemapJob

### Tests
- SEO metadata rendering test.
- Sitemap generation integrity test.

## H. Security and Audit Module
### Migration
- audit_logs, security_events, file_upload_logs.

### Service
- AuditLogService
- TamperRiskService
- UploadValidationService

### Controller/API
- AuditController
- SecurityEventController
- UploadController

### Job/Event
- SecurityAnomalyScanJob
- CleanupRetentionJob

### Tests
- Upload mime tamper rejection test.
- Tamper risk score blocking test.
