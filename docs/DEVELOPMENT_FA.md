# راهنمای توسعه

## معماری کلی

پروژه یک Laravel monolith است. لایه‌های اصلی:

- `routes/`: تعریف endpointهای web/public/settings
- `app/Http/Controllers/`: orchestration و application flow
- `app/Models/`: مدل‌های Eloquent و relationها
- `app/DataTables/`: query و presentation جدول‌های server-side
- `app/Helper/`: helperهای مشترک
- `app/Notifications/`: اعلان‌ها
- `database/migrations/`: versioning schema
- `database/seeders/`: داده‌های پایه
- `resources/views/`: Blade UI
- `tests/`: regression و behavior tests

## قاعده‌ی HTTP

Endpointهایی که state را تغییر می‌دهند باید GET نباشند.

- GET: نمایش، modal، list، export یا query بدون mutation
- POST: command/create/action
- PUT/PATCH: update
- DELETE: delete

هر caller جاوااسکریپتی باید verb متناظر را ارسال کند و CSRF Laravel را حفظ کند.

## Authorization

قبل از mutation:

1. resource را load کنید.
2. Permission/ownership را بررسی کنید.
3. در صورت عدم دسترسی `abort_403` یا Policy مناسب اجرا شود.
4. سپس mutation انجام شود.

Permissionهای company-scoped باید `company_id` و Global Scopeهای پروژه را در نظر بگیرند.

## تاریخ جلالی

### اصل طراحی

DB، Queue، API و Webhook باید Gregorian/ISO باقی بمانند. جلالی فقط در presentation استفاده شود.

### Helperهای استاندارد

```php
ctj($value, 'Y/m/d');
ctj_datetime($value, 'Y/m/d H:i');
jalali_date($value, 'Y/m/d');
```

در locale غیر فارسی helper مقدار قبلی را حفظ می‌کند. ورودی نامعتبر نیز نباید exception عمومی UI ایجاد کند.

برای inputهایی که backend انتظار Gregorian دارد، فقط به دلیل نمایش فارسی مقدار persistence را به Jalali تبدیل نکنید؛ conversion باید با مسیر استاندارد `companyToYmd` یا converter موجود انجام شود.

## Migration

Migration production-safe باید:

- وجود table/column را در migrationهای repair/legacy بررسی کند؛
- destructive operation غیرضروری نداشته باشد؛
- index/foreign key را با نام قابل پیش‌بینی مدیریت کند؛
- rollbackی که باعث از دست رفتن داده می‌شود ارائه نکند؛ در این موارد `down()` می‌تواند عمداً non-destructive باشد و توضیح داشته باشد.

SQL موقت یا دستور manual schema نباید در root repository جایگزین Migration شود.

## Seeder

Seeder باید اجرای چندباره را تحمل کند:

```php
Model::query()->updateOrCreate([...], [...]);
Model::query()->firstOrCreate([...], [...]);
```

برای tenant/company زیاد از `chunkById()` استفاده کنید.

`DatabaseSeeder` نباید:

- APP_KEY را rotate کند؛
- داده‌ی production را بدون شرط حذف کند؛
- state موقت config را بدون `finally` باقی بگذارد.

## Composer

- `*` برای packageهای runtime ممنوع است مگر virtual package استاندارد.
- dependency از branch توسعه باید به commit معلوم pin شود.
- `composer.lock` باید commit شود.
- قبل از merge:

```bash
composer validate --no-check-publish
composer audit --locked --no-dev
```

## Webhook

Webhook endpointها معمولاً CSRF token مرورگر ندارند؛ بنابراین exemption فقط برای endpoint دقیق provider مجاز است.

هم‌زمان باید validation سطح provider برقرار باشد:

- Stripe signature
- Razorpay secret/signature
- Flutterwave verification
- PayPal verification
- سایر providerها بر اساس مستندات رسمی خودشان

Wildcard سراسری مثل `*-webhook/*` ممنوع است چون endpoint آینده را ناخواسته exempt می‌کند.

## تست

Bugfixهای infrastructure/security باید حداقل regression test داشته باشند.

نمونه دسته‌ها:

- Jalali conversion
- mutation route verbs
- CSRF exemption scope
- Composer reproducibility
- وجود migration repair و حذف scratch SQL

اجرای تست:

```bash
php artisan test --testsuite=Unit
```

## CI

Workflow کیفیت باید این مراحل را داشته باشد:

1. Checkout
2. PHP setup
3. Composer validate
4. Composer install
5. Composer security audit
6. Unit tests
7. PHP syntax lint

Merge روی `main` فقط بعد از سبز بودن validation انجام شود.

## Checklist قبل از PR

- [ ] migration نوشته شده و schema دستی وجود ندارد
- [ ] seeder idempotent است
- [ ] GET mutation وجود ندارد
- [ ] authorization بررسی شده
- [ ] Jalali فقط presentation را تغییر داده
- [ ] secret در کد commit نشده
- [ ] Unit test اضافه/به‌روز شده
- [ ] Composer validate موفق است
- [ ] Composer audit بررسی شده
- [ ] PHP lint موفق است
- [ ] داکیومنت در صورت تغییر رفتار به‌روز شده
