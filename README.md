# Project Management / Worksuite Customization

این مخزن یک سامانه‌ی جامع مدیریت سازمان، پروژه، منابع انسانی، CRM و مالی مبتنی بر Laravel است. نسخه‌ی فعلی برای زبان فارسی، RTL و نمایش تاریخ جلالی بهینه شده و شامل hardening امنیتی، Seederهای قابل‌تکرار و CI برای کنترل کیفیت است.

> این پروژه بر پایه‌ی Worksuite توسعه یافته است. سازوکارهای لایسنس و فعال‌سازی نرم‌افزارهای شخص ثالث باید مطابق مجوز اصلی آن‌ها باقی بماند.

## امکانات اصلی

- مدیریت چندشرکتی و تنظیمات سازمانی
- کاربران، نقش‌ها و Permissionهای سطح دسترسی
- کارمندان، دپارتمان‌ها و سمت‌ها
- پروژه، Task، Milestone، فایل و فعالیت‌ها
- حضور و غیاب، Clock In/Out، QR Attendance، شیفت و Timesheet
- مرخصی، تعطیلات و گردش تأیید
- CRM شامل Lead، Deal، Client و Contact
- قراردادها و امضای قرارداد
- Invoice، Recurring Invoice، Estimate، Proposal و Credit Note
- Expense، Payment، Bank Transaction و گزارش‌های مالی
- Product، Cart و Order
- Ticket و پشتیبانی
- اعلان‌های ایمیلی و تنظیمات SMTP
- Webhook و درگاه‌های پرداخت متعدد
- Backup، Storage و تنظیمات GDPR
- زبان فارسی، RTL و نمایش تاریخ جلالی در لایه‌ی Presentation
- Seederهای idempotent برای نصب و بازسازی محیط
- GitHub Actions برای Composer validation، audit، تست واحد و PHP lint

فهرست کامل‌تر قابلیت‌ها در [`docs/FEATURES_FA.md`](docs/FEATURES_FA.md) آمده است.

## تکنولوژی

- PHP 8.1+
- Laravel 10
- MySQL / MariaDB
- Blade + JavaScript/jQuery
- Bootstrap-based UI
- Composer
- Hekmatinasser Verta برای تاریخ جلالی
- PHPUnit / Laravel Test Runner
- GitHub Actions

## نصب توسعه

```bash
composer install
cp .env.example .env
php artisan key:generate
```

سپس اتصال دیتابیس، Mail، Queue، Cache و سرویس‌های موردنیاز را در `.env` تنظیم کنید:

```bash
php artisan migrate
php artisan db:seed
php artisan optimize:clear
```

برای محیط production از فلگ‌های اجباری استفاده کنید:

```bash
php artisan migrate --force
php artisan db:seed --force
```

**نکته:** `DatabaseSeeder` دیگر APP_KEY را تغییر نمی‌دهد. `key:generate` فقط در زمان ایجاد محیط جدید اجرا شود، نه هنگام seed کردن دیتابیس موجود.

## تست و کنترل کیفیت

```bash
composer validate --no-check-publish
composer audit --locked --no-dev
php artisan test --testsuite=Unit
find app database routes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

همین کنترل‌ها در GitHub Actions نیز اجرا می‌شوند.

## تاریخ جلالی

در locale فارسی، نمایش تاریخ باید از helperهای مرکزی استفاده کند:

```php
ctj($date, 'Y/m/d');
ctj_datetime($date, 'Y/m/d H:i');
jalali_date($date, 'Y/m/d');
```

قاعده‌ی پروژه این است که دیتابیس و API تاریخ را Gregorian/ISO نگه دارند و تبدیل جلالی فقط در لایه‌ی نمایش انجام شود. این موضوع از خراب‌شدن Query، Queue، Payment Callback و Integration جلوگیری می‌کند.

## Seederها

Seeder اصلی:

```bash
php artisan db:seed
```

ویژگی‌های مهم Seederهای فعلی:

- اجرای قابل‌تکرار با `firstOrCreate` / `updateOrCreate`
- Seed کامل تنظیمات Email Notification از catalog مدل
- Seed نقش‌ها و Permissionها با رعایت `company_id`
- پردازش Companyها به‌صورت chunk برای مصرف حافظه کمتر
- reset شدن `app.seeding` حتی در صورت exception
- عدم تغییر APP_KEY

## امنیت

- عملیات state-changing نباید با GET انجام شوند؛ Routeهای قطعی به POST تبدیل شده‌اند.
- CSRF exemptionهای Webhook به callbackهای مشخص providerها محدود شده‌اند.
- Provider webhookها باید علاوه بر CSRF exemption، signature/secret خود سرویس را validate کنند.
- وابستگی‌های مستقیم Composer تا حد ممکن با نسخه/commit مشخص محدود شده‌اند.
- فایل SQL موقت ریشه‌ی پروژه حذف و تغییرات schema به Migration منتقل شده‌اند.

## ساختار مهم پروژه

```text
app/
  Http/Controllers/       Controllerها
  Models/                 مدل‌ها
  Helper/                 Helperهای عمومی و Jalali
  DataTables/             DataTableهای server-side
  Notifications/          اعلان‌ها

database/
  migrations/             Migrationهای دیتابیس
  seeders/                Seederهای نصب و داده پایه

resources/views/          Blade views
routes/                   Routeهای web/public/settings

tests/                    Unit و Feature tests
.github/workflows/         CI workflows
docs/                     مستندات پروژه
```

## مستندات

- [`docs/FEATURES_FA.md`](docs/FEATURES_FA.md) — امکانات و ماژول‌ها
- [`docs/DEVELOPMENT_FA.md`](docs/DEVELOPMENT_FA.md) — معماری، توسعه، تاریخ جلالی، امنیت و تست
- [`docs/DEPLOYMENT_FA.md`](docs/DEPLOYMENT_FA.md) — Runbook نصب، Deploy، Migration، Queue و Rollback
- [`AUDIT_2026-08-31.md`](AUDIT_2026-08-31.md) — گزارش Audit و hardening انجام‌شده

## روند توسعه پیشنهادی

1. Branch جدید از `main` بسازید.
2. Migration/Seeder را idempotent و backward-compatible بنویسید.
3. تغییر state را فقط با POST/PUT/PATCH/DELETE انجام دهید.
4. برای تاریخ فارسی از helper مرکزی استفاده کنید.
5. Unit/Feature test اضافه کنید.
6. CI باید سبز باشد.
7. سپس Pull Request را merge کنید.
