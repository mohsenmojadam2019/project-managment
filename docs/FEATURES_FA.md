# امکانات پروژه

این سند قابلیت‌هایی را که در Routeها، Controllerها، Modelها، Viewها و تنظیمات پروژه مشاهده می‌شوند دسته‌بندی می‌کند. فعال بودن بعضی قابلیت‌ها می‌تواند به Permission، تنظیمات Company، ماژول نصب‌شده یا Credential سرویس خارجی وابسته باشد.

## 1. مدیریت سازمان و دسترسی

- Multi-company / Company settings
- کاربران و پروفایل‌ها
- Role و Permission
- نقش‌های پایه مانند Manager و Project Admin
- کنترل دسترسی روی عملیات CRUD
- تنظیم زبان، timezone، date/time format و RTL
- مدیریت تنظیمات عمومی شرکت

## 2. منابع انسانی

- Employee management
- Department / Team
- Designation / سمت سازمانی
- Attendance
- Clock In / Clock Out
- QR Attendance
- Attendance report و export
- Employee Shift
- Shift Roster
- Weekly Timesheet
- Leave و گردش approve/reject
- Holiday
- گزارش ساعت کاری

## 3. مدیریت پروژه

- Project
- Project archive و restore
- Duplicate project
- Task
- Task status و approval flow
- Milestone
- Project files
- Project activity / overview
- Project-related invoice و time log
- دسترسی پروژه بر اساس نقش و مالکیت

## 4. CRM و فروش

- Lead
- Lead Contact
- Deal
- Deal Stage
- Client / Customer
- Client Contact
- Estimate Request
- Estimate
- Proposal
- Contract
- Contract Signature
- ارتباط CRM با پروژه و اسناد مالی

## 5. مالی و حسابداری عملیاتی

- Invoice
- Recurring Invoice
- Invoice items و attachment/image
- Payment reminder
- Payment
- Credit Note
- Expense
- Recurring Expense
- Bank transaction
- Tax و Unit Type
- Currency
- Shipping address روی Invoice
- Financial reports
- PDF generation برای اسناد مالی

## 6. محصول و سفارش

- Product catalog
- Cart
- Empty cart flow
- Order
- Order invoice/PDF
- اتصال محصول به Invoice/Credit Note items

## 7. پشتیبانی

- Ticket
- Ticket activity
- Public ticket form
- Notificationهای مرتبط با Ticket

## 8. اعلان و ارتباطات

- SMTP settings
- Email settings
- Email Notification catalog
- Notificationهای رخدادمحور برای پروژه، Task، Invoice، Ticket، Leave و سایر ماژول‌ها
- Slack webhook configuration
- Push/Pusher settings در seed پایه

## 9. پرداخت و Webhook

Route و Controller برای چند provider پرداخت در پروژه وجود دارد، از جمله:

- Stripe
- PayPal
- Razorpay
- Paystack
- Flutterwave
- Mollie
- PayFast
- Square

Webhookهای providerها از CSRF معمول Laravel مستثنا هستند، اما exemption به مسیرهای مشخص provider محدود شده است. Controller هر provider باید signature/secret مخصوص همان سرویس را validate کند.

## 10. گزارش و خروجی

- Attendance export
- Time log reports
- Invoice/Estimate/Order PDF
- Dashboard summaries
- DataTableهای server-side
- گزارش‌های مالی و عملیاتی

## 11. فایل، Backup و Storage

- File upload در چند ماژول
- Storage setting
- Database backup settings
- Backup retention configuration
- Local storage seed پایه

## 12. GDPR و حریم خصوصی

- GDPR settings
- Lead removal/approval request
- Customer removal/approval request
- گردش approve/reject برای درخواست‌های مرتبط

## 13. فارسی و جلالی

- زبان فارسی (`fa`)
- RTL
- Verta
- helper مرکزی `ctj()`
- helper `ctj_datetime()`
- alias `jalali_date()`
- حفظ Gregorian/ISO در persistence و integration
- تبدیل جلالی در presentation layer

## 14. Seeder و Bootstrap محیط

Seeder اصلی اکنون برای اجرای چندباره طراحی شده است:

- Countries
- SMTP
- Email settings
- Core database settings
- Module permissions
- Organisation settings
- Company-scoped seeders
- Roles / permissions
- Notification catalog

اصول Seeder:

- عدم چرخاندن APP_KEY
- idempotent بودن
- chunk کردن Companyها
- reset کردن state در `finally`

## 15. کیفیت و CI

GitHub Actions برای کنترل موارد زیر وجود دارد:

- `composer validate`
- `composer audit`
- dependency installation
- Unit tests
- PHP syntax lint
- auditهای repository-level برای hardening

## 16. نکات توسعه

برای افزودن قابلیت جدید:

- تغییر schema فقط با Migration
- داده‌ی پایه فقط با Seeder idempotent
- عملیات mutation با POST/PUT/PATCH/DELETE
- Ruleهای Authorization قبل از mutation
- تاریخ جلالی فقط در UI/Presentation
- تست regression برای bugfixهای مهم
- Credential و secret خارج از repository و در `.env`/secret manager
