# ⚡ مرکز کنترل فروش

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

صفحهٔ **مرکز کنترل فروش** (`panel/sales_control.php`) یک داشبورد یکپارچه برای مدیر است: دیدِ کامل از فروش + کلیدهای کنترل لحظه‌ای رفتار ربات + اتصال مستقیم به ربات اصلی. آیتم آن در ساید‌بار با عنوان **«مرکز کنترل فروش»** قرار دارد.

```
https://<domain>/panel/sales_control.php
```

## 📊 KPIهای فروش
- **درآمد و تعداد سفارش:** امروز / ۷روز / ۳۰روز / کل (روی فاکتورهای واقعیِ فروخته‌شده، بدون «سرویس تست»).
- **سرویس‌های فعال** (وضعیت‌های زنده: `active, end_of_time, end_of_volume, sendedwarn, send_on_hold`).
- **کاربران جدید ۲۴ ساعت اخیر.**
- **بدهی کیف‌پول کاربران** (`SUM(GREATEST(0, Balance))`).
- **پرفروش‌ترین محصولات** (۳۰ روز).
- **آخرین تراکنش‌ها** (`Payment_report`).

## 🎛️ کلیدهای سریع کنترل فروش
این کلیدها مستقیماً فلگ‌های واقعی جدول `shopSetting` را که ربات می‌خواند تغییر می‌دهند (با محافظت CSRF):

| کلید | فلگ | کاربرد |
|------|-----|--------|
| خرید مستقیم | `statusdirectpabuy` | فعال/غیرفعال‌کردن خرید مستقیم |
| نمایش قیمت | `statusshowprice` | نمایش/مخفی‌کردن قیمت‌ها |
| حجم اضافه | `statusextra` | فروش حجم اضافه |
| زمان اضافه | `statustimeextra` | فروش زمان اضافه |
| تغییر سرویس | `statuschangeservice` | اجازهٔ تغییر سرویس |
| نمایش کانفیگ | `configshow` | نمایش کانفیگ به کاربر |
| گزارش اختلال | `statusdisorder` | حالت اختلال/توقف موقت فروش |

> تغییرات بلافاصله توسط ربات اعمال می‌شوند (در درخواست بعدی از دیتابیس خوانده می‌شوند، بدون کش).

## 🤖 اتصال ربات اصلی
- **وضعیت ربات (`getMe`)**: نمایش آنلاین‌بودن + یوزرنیم ربات.
- **وضعیت وب‌هوک (`getWebhookInfo`)**: آدرس فعلی، تعداد آپدیت‌های در صف، و **آخرین خطای وب‌هوک**.
- **ثبت/به‌روزرسانی وب‌هوک**: فیلد از پیش با آدرس فعلی پر می‌شود (یا `APP_ORIGIN/index.php`) و فقط `https://` پذیرفته می‌شود تا وب‌هوک سالم اشتباهاً خراب نشود.
- **ارسال پیام تست به ادمین**: برای تأیید سریع اتصال (به‌خصوص بعد از تنظیم **[پراکسی](Proxy-Setup)**).

## 🧷 امنیت و پایداری
- همهٔ اکشن‌های POST با `hash_equals` روی توکن CSRF نشست اعتبارسنجی می‌شوند.
- همهٔ تماس‌های دیتابیس/تلگرام داخل try/catch با fallback امن هستند؛ صفحه روی نبودِ ستون/جدول یا آفلاین‌بودن ربات کرش نمی‌کند.

---

<a name="-english"></a>
## 🇬🇧 English — Sales Control Center

`panel/sales_control.php` is a unified admin dashboard: full sales visibility + live bot‑behavior toggles + a direct connection to the main bot. Sidebar item: **“Sales Control Center”**.

### 📊 KPIs
Revenue & order counts (today/7d/30d/total, real sold invoices excluding the test service), active services (live statuses), new users (24h), user wallet liability, top products (30d), recent transactions (`Payment_report`).

### 🎛️ Quick sales controls
CSRF‑protected toggles writing the real `shopSetting` flags the bot reads: `statusdirectpabuy, statusshowprice, statusextra, statustimeextra, statuschangeservice, configshow, statusdisorder`. Changes take effect immediately (read from DB on the next request, no cache).

### 🤖 Main bot connection
- `getMe` — online status + bot username.
- `getWebhookInfo` — current URL, pending updates, last webhook error.
- Set/refresh webhook — prefilled with the current URL (or `APP_ORIGIN/index.php`), `https://`‑validated so a working webhook isn’t clobbered.
- Send a test message to the admin — quick connectivity check (handy after **[proxy](Proxy-Setup)** setup).

### Security & resilience
All POST actions validate the session CSRF token via `hash_equals`; all DB/Telegram calls are wrapped in try/catch with safe fallbacks, so the page never fatals on a missing column/table or an offline bot.
