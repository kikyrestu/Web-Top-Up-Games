# 📁 Project Structure — Web PPOB & Top Up Games

## Tech Stack
- **Backend:** Laravel 11
- **Frontend:** Laravel Blade + Alpine.js + TailwindCSS
- **Database:** MySQL (via Laragon)
- **Payment Gateway:** Configurable (Tripay / Midtrans / Xendit)
- **API Provider PPOB:** Configurable (Digiflazz / Rajabiller / MTIX / custom)

---

## Laravel Directory Structure

```
webppobdantopup/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── TransactionController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── ProviderController.php
│   │   │   │   ├── PaymentGatewayController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── SettingController.php
│   │   │   ├── Customer/
│   │   │   │   ├── HomeController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── CheckoutController.php
│   │   │   │   ├── TransactionController.php
│   │   │   │   └── PaymentController.php
│   │   │   └── Webhook/
│   │   │       ├── PaymentWebhookController.php
│   │   │       └── ProviderCallbackController.php
│   │   ├── Middleware/
│   │   │   ├── AdminMiddleware.php
│   │   │   └── MaintenanceModeMiddleware.php
│   │   └── Requests/
│   │       ├── Admin/
│   │       └── Customer/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Transaction.php
│   │   ├── TransactionItem.php
│   │   ├── PaymentGateway.php
│   │   ├── ApiProvider.php
│   │   ├── Setting.php
│   │   └── ActivityLog.php
│   ├── Services/
│   │   ├── Provider/
│   │   │   ├── ProviderInterface.php
│   │   │   ├── DigiflazzService.php
│   │   │   ├── RajabillerService.php
│   │   │   └── CustomProviderService.php
│   │   ├── Payment/
│   │   │   ├── PaymentInterface.php
│   │   │   ├── TripayService.php
│   │   │   ├── MidtransService.php
│   │   │   └── XenditService.php
│   │   ├── TransactionService.php
│   │   └── NotificationService.php
│   └── Helpers/
│       └── AppHelper.php
│
├── config/
│   └── ppob.php
│
├── database/
│   ├── migrations/
│   │   ├── create_categories_table.php
│   │   ├── create_products_table.php
│   │   ├── create_transactions_table.php
│   │   ├── create_transaction_items_table.php
│   │   ├── create_api_providers_table.php
│   │   ├── create_payment_gateways_table.php
│   │   ├── create_settings_table.php
│   │   └── create_activity_logs_table.php
│   └── seeders/
│       ├── CategorySeeder.php
│       ├── ProductSeeder.php
│       └── AdminSeeder.php
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── admin.blade.php
│       │   └── customer.blade.php
│       ├── admin/
│       │   ├── dashboard/
│       │   ├── products/
│       │   ├── categories/
│       │   ├── transactions/
│       │   ├── users/
│       │   ├── providers/
│       │   ├── payment-gateways/
│       │   ├── reports/
│       │   └── settings/
│       └── customer/
│           ├── home/
│           ├── products/
│           ├── checkout/
│           ├── payment/
│           └── transaction/
│
├── routes/
│   ├── web.php
│   ├── admin.php
│   └── webhook.php
│
├── public/
│   ├── assets/
│   │   ├── images/
│   │   ├── css/
│   │   └── js/
│   └── index.php
│
└── docs/                          ← (folder ini)
    ├── 01_project_structure.md
    ├── 02_features_list.md
    └── 03_implementation_plan.md
```

---

## Database Schema Overview

| Table | Description |
|---|---|
| `users` | Admin & customer accounts |
| `categories` | Kategori produk (Games, Pulsa, PPOB, dll) |
| `products` | Daftar produk dengan harga modal & jual |
| `transactions` | Header transaksi customer |
| `transaction_items` | Detail item per transaksi |
| `api_providers` | Konfigurasi provider API (Digiflazz, dll) |
| `payment_gateways` | Konfigurasi payment gateway |
| `settings` | Konfigurasi umum aplikasi (key-value) |
| `activity_logs` | Log aktivitas admin & sistem |
