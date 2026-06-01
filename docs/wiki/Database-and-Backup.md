# 🗄️ دیتابیس، بکاپ و انتقال

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

## 🧬 ساختار دیتابیس
- دیتابیس **خودترمیم** است: ستون‌های گم‌شده در زمان اجرا به‌صورت خودکار اضافه می‌شوند (`panel/lib/schema.php`). بنابراین بعد از آپدیت معمولاً نیازی به مهاجرت دستی نیست.
- جداول کلیدی: `user` (کاربران/کیف‌پول)، `setting` (تنظیمات + پراکسی)، `shopSetting` (کلیدهای رفتار فروش)، `product`/`invoice` (محصول/فاکتور)، `Payment_report` (تراکنش‌ها)، `reseller`/`reseller_ledger` (نمایندگان/دفترکل)، پنل‌ها (`marzban_panel` و …).

## 💾 بکاپ
- **روش اسکریپت (سرور):** دستور `faoxima` را بزنید و گزینهٔ بکاپ را انتخاب کنید (کرون `cronbot/*` هم بکاپ خودکار دارد).
- **روش دستی (mysqldump):**
  ```bash
  mysqldump -u <user> -p <dbname> > faoxima_backup_$(date +%F).sql
  ```
- **هاست (cPanel/aaPanel):** از **phpMyAdmin → Export** کل دیتابیس را خروجی بگیرید.

## 🔁 بازگردانی / انتقال به سرور یا هاست جدید
### روی سرور
1. اسکریپت را روی سرور جدید نصب کنید (گزینهٔ Install).
2. وارد **phpMyAdmin** شوید (اطلاعات در `/root/conffaoxima/dbrootfaoxima.txt`).
3. دیتابیس را **DROP** کرده و بکاپ خود را **Import** کنید.
4. اگر ربات کار نکرد، از منوی `faoxima` گزینهٔ **Update** را بزنید.

### روی هاست (cPanel / aaPanel)
1. سورس را آپلود و **Installer** را اجرا کنید تا جداول ساخته شوند.
2. وارد **phpMyAdmin** شوید، دیتابیس را **DROP** و بکاپ را **Import** کنید.
3. پوشهٔ `installer` را حذف کنید. 🔒
4. مقادیر `config.php` (دیتابیس/توکن/دامنه) را با محیط جدید هماهنگ کنید.
5. در صورت نیاز یک‌بار `https://<domain>/index.php` را باز کنید تا وب‌هوک ثبت شود.

## ⚠️ نکات مهم هنگام انتقال
- بعد از انتقال دامنه، حتماً **`$domainhosts`** و **وب‌هوک** را به‌روزرسانی کنید (از [مرکز کنترل فروش](Sales-Control-Center)).
- اگر روی سرور/هاست ایران منتقل کردید، **[پراکسی](Proxy-Setup)** را تنظیم کنید.
- قبل از حذف سورس قدیمی، `config.php` را ذخیره کنید.

---

<a name="-english"></a>
## 🇬🇧 English — Database, backup & migration

### Schema
The DB is **self‑healing**: missing columns are added at runtime (`panel/lib/schema.php`), so manual migrations are usually unnecessary after updates. Key tables: `user`, `setting`, `shopSetting`, `product`/`invoice`, `Payment_report`, `reseller`/`reseller_ledger`, panel tables (`marzban_panel`, …).

### Backup
- **Server:** run `faoxima` → backup option (cron `cronbot/*` also auto‑backs up).
- **Manual:** `mysqldump -u <user> -p <dbname> > backup.sql`.
- **Host:** phpMyAdmin → Export.

### Restore / migrate
**Server:** install on the new server → phpMyAdmin (creds in `/root/conffaoxima/dbrootfaoxima.txt`) → DROP DB → Import backup → run **Update** if needed.
**Host:** upload source → run **Installer** (creates tables) → phpMyAdmin DROP + Import → delete `installer` 🔒 → align `config.php` → open `index.php` once if needed.

### Migration notes
After a domain change, update **`$domainhosts`** and the **webhook** (from the [Sales Control Center](Sales-Control-Center)). For Iran hosts, configure the **[proxy](Proxy-Setup)**. Save `config.php` before deleting the old source.
