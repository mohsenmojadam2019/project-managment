# Runbook استقرار و نگهداری

## پیش‌نیاز

- PHP سازگار با `composer.json` (حداقل 8.1)
- Composer 2
- MySQL/MariaDB
- Web server مانند Nginx/Apache
- Queue worker در صورت استفاده از Queue
- Scheduler برای `schedule:run`
- دسترسی نوشتن Laravel به `storage/` و `bootstrap/cache/`

## استقرار اولیه

```bash
git clone <repository>
cd project-managment
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

مقادیر production را در `.env` تنظیم کنید، به‌خصوص:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- Database
- Cache / Session
- Queue
- Mail
- Filesystem
- Payment credentials
- Webhook secrets

سپس:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## نکته APP_KEY

APP_KEY فقط یک بار برای محیط جدید ساخته شود. Seeder پروژه APP_KEY را تغییر نمی‌دهد.

در محیط موجود هرگز بدون برنامه‌ی migration رمزنگاری، `php artisan key:generate` اجرا نکنید؛ تغییر کلید می‌تواند session/cookie و داده‌های encrypted را غیرقابل‌خواندن کند.

## Deploy نسخه جدید

ترتیب پیشنهادی:

```bash
git fetch --all --prune
git checkout main
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

اگر deployment zero-downtime دارید، migrationهای طولانی و queue restart را مطابق orchestration همان محیط اجرا کنید.

## کنترل بعد از Deploy

```bash
php artisan about
php artisan migrate:status
php artisan test --testsuite=Unit
```

همچنین بررسی کنید:

- login
- dashboard
- project/task CRUD
- attendance/leave
- invoice/estimate/proposal
- mail delivery
- queue processing
- payment callback/webhook در sandbox provider
- نمایش فارسی و تاریخ جلالی

## Queue

نمونه worker:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=120
```

در production ترجیحاً Supervisor/Systemd استفاده شود.

پس از deploy:

```bash
php artisan queue:restart
```

## Scheduler

Cron نمونه:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Backup

قبل از migration مهم:

1. Backup دیتابیس بگیرید.
2. فایل‌های storage مهم را snapshot کنید.
3. commit/SHA در حال deploy را ثبت کنید.
4. migration status را ثبت کنید.

## Rollback

Rollback کد:

```bash
git checkout <known-good-sha>
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Migration rollback فقط وقتی انجام شود که `down()` واقعاً safe و non-destructive باشد. Migrationهای repair که عمداً `down()` غیرتخریبی دارند با restore دیتابیس/forward fix مدیریت شوند.

## Webhook در Production

- URL provider باید HTTPS باشد.
- secret/signature در `.env` یا secret manager ذخیره شود.
- callbackهای payment باید POST باشند مگر endpoint صرفاً برای نمایش/verification URL باشد.
- CSRF exemption فقط برای path مشخص provider باشد.
- logها نباید secret یا payload حساس کامل را چاپ کنند.

## بررسی Composer

قبل از هر release:

```bash
composer validate --no-check-publish
composer audit --locked --no-dev
```

در صورت vulnerability جدید، بدون بررسی compatibility packageها را کورکورانه major-upgrade نکنید؛ ابتدا branch، CI و staging.

## CI Release Gate

قبل از merge/deploy باید حداقل این‌ها سبز باشند:

```bash
composer validate --no-check-publish
composer install --no-interaction --prefer-dist --no-progress
composer audit --locked --no-dev
php artisan test --testsuite=Unit
find app database routes tests -type f -name '*.php' -print0 | xargs -0 -n1 php -l
```

## Incident checklist

اگر بعد از deploy خطا رخ داد:

1. SHA فعلی را ثبت کنید.
2. Laravel log و web server error log را بررسی کنید.
3. Queue failed jobs را بررسی کنید.
4. Migration status را بررسی کنید.
5. آخرین تغییر `.env`/secret را بدون افشای مقدار بررسی کنید.
6. در صورت regression کد، به known-good SHA برگردید.
7. اگر schema تغییر کرده، forward fix یا restore کنترل‌شده انجام دهید.
