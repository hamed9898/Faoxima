# ⚙️ تنظیمات اولیه (config.php)

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

فایل `config.php` در ریشهٔ سورس، اتصال دیتابیس و هویت ربات را تعریف می‌کند. در نصب با اسکریپت/Installer این فایل خودکار پر می‌شود؛ اما دانستن فیلدها برای رفع مشکل ضروری است.

## 🔑 فیلدهای کلیدی

| متغیر | توضیح | نمونه |
|------|-------|-------|
| `$dbname` | نام دیتابیس MySQL | `faoxima` |
| `$usernamedb` | کاربر دیتابیس | `faoxima_user` |
| `$passworddb` | رمز دیتابیس | `••••••` |
| `$APIKEY` | توکن ربات اصلی از BotFather | `123456:ABC-...` |
| `$adminnumber` | **Chat ID عددی** ادمین (نه یوزرنیم) | `123456789` |
| `$domainhosts` | دامنه بدون `https://` و بدون `/` انتهایی | `bot.example.com` |
| `$usernamebot` | یوزرنیم ربات بدون `@` | `myvpnbot` |
| `$telegramCurlTimeout` | تایم‌اوت درخواست‌های تلگرام (ثانیه) | `10` |
| `$telegramStrictIpValidation` | اعتبارسنجی سخت‌گیرانهٔ IP وب‌هوک تلگرام | `true` |

> ⚠️ `$domainhosts` به‌صورت خودکار `https://` و `/` انتهایی را حذف می‌کند و ثابت `APP_ORIGIN = https://<domain>` را می‌سازد که در ساخت لینک‌های وب‌هوک و سابسکریپشن استفاده می‌شود.

## 🧩 نمونهٔ پرشده
```php
$dbname     = 'faoxima';
$usernamedb = 'faoxima_user';
$passworddb = 'STRONG_PASSWORD';

$APIKEY      = '123456789:AAH...your-bot-token';
$adminnumber = '123456789';      // numeric chat id
$domainhosts = 'bot.example.com'; // no scheme, no trailing slash
$usernamebot = 'myvpnbot';        // no @
```

## 🪝 وب‌هوک ربات اصلی
آدرس وب‌هوک ربات اصلی همان `index.php` است:
```
https://<domain>/index.php
```
برای ثبت/بررسی آن از **[مرکز کنترل فروش](Sales-Control-Center)** استفاده کنید (دکمهٔ ثبت وب‌هوک + نمایش `getWebhookInfo`).

## 🔌 پراکسی
انتهای `config.php` فایل `proxy.php` را require می‌کند. تنظیمات پراکسی در **دیتابیس** (جدول `setting`) نگهداری و از پنل مدیر اعمال می‌شود — نه در `config.php`. برای جزئیات: **[تنظیم پراکسی](Proxy-Setup)**.

## 🛡️ نکات امنیتی
- پوشهٔ `installer` را بعد از نصب حذف کنید. 🔒
- `config.php` را خارج از دسترس عمومی نگه دارید (سورس به‌صورت پیش‌فرض آن را مستقیماً سرو نمی‌کند، اما از مجوزهای فایل مناسب مطمئن شوید).
- از HTTPS معتبر استفاده کنید؛ تلگرام وب‌هوک HTTP را نمی‌پذیرد.

---

<a name="-english"></a>
## 🇬🇧 English — Initial configuration (config.php)

`config.php` (repo root) defines the DB connection and bot identity. The installer fills it automatically, but knowing the fields helps with troubleshooting.

### 🔑 Key fields
| Variable | Description | Example |
|---|---|---|
| `$dbname` / `$usernamedb` / `$passworddb` | MySQL database, user, password | `faoxima` |
| `$APIKEY` | Main bot token (BotFather) | `123456:ABC-...` |
| `$adminnumber` | Admin **numeric** chat ID | `123456789` |
| `$domainhosts` | Domain without scheme/trailing slash | `bot.example.com` |
| `$usernamebot` | Bot username (no `@`) | `myvpnbot` |
| `$telegramCurlTimeout` | Telegram request timeout (s) | `10` |
| `$telegramStrictIpValidation` | Strict Telegram webhook IP check | `true` |

`$domainhosts` is auto‑normalized and exposed as `APP_ORIGIN = https://<domain>`, used for webhook and subscription links.

### 🪝 Main bot webhook
The main bot webhook is `https://<domain>/index.php`. Register/check it from the **[Sales Control Center](Sales-Control-Center)**.

### 🔌 Proxy
`config.php` requires `proxy.php`. Proxy settings live in the DB (`setting` table) and are managed from the admin panel — see **[Proxy setup](Proxy-Setup)**.

### 🛡️ Security
Delete `installer` after setup, keep `config.php` protected, and always use valid HTTPS (Telegram rejects non‑HTTPS webhooks).
