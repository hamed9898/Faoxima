# 🔌 تنظیم پراکسی (هاست‌های ایران)

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

برای مدیرانی که سورس را روی **هاست/سرور ایران** نصب می‌کنند و دسترسی مستقیم به تلگرام یا پنل‌های خارج از کشور ندارند، Faoxima از **پراکسی خروجی** پشتیبانی می‌کند — به‌صورت **جداگانه** برای اتصال به تلگرام و برای اتصال به پنل‌ها.

## 🧭 جایی که تنظیم می‌شود
پنل مدیر → **تنظیمات (settings.php)** → بخش **«پراکسی (برای هاست‌های ایران)»**.

تنظیمات در جدول `setting` ذخیره می‌شوند (`proxy_telegram_status/url`, `proxy_panel_status/url`).

## 🎛️ فیلدها
| فیلد | کاربرد |
|------|--------|
| **استفاده از پراکسی برای تلگرام** | روشن/خاموش‌کردن پراکسی برای همهٔ درخواست‌های Bot API |
| **آدرس پراکسی تلگرام** | `scheme://[user:pass@]host:port` |
| **استفاده از پراکسی برای پنل‌ها** | روشن/خاموش‌کردن پراکسی برای اتصال به پنل‌های VPN |
| **آدرس پراکسی پنل‌ها** | همان قالب؛ می‌تواند همان پراکسی تلگرام باشد |

## 📝 قالب آدرس پراکسی
```
scheme://[user:pass@]host:port
```
**scheme**های پشتیبانی‌شده: `http`, `socks4`, `socks4a`, `socks5`, `socks5h`.
- اگر scheme ننویسید، **http** فرض می‌شود.
- برای رد کردن DNS از داخل ایران (resolve در سمت پراکسی)، **`socks5h`** توصیه می‌شود.

### نمونه‌ها
```
socks5h://1.2.3.4:1080
socks5h://user:pass@1.2.3.4:1080
http://10.0.0.5:8080
```

## ✅ مراحل پیشنهادی
1. یک پراکسی سالم خارج از ایران تهیه کنید (SOCKS5 توصیه می‌شود).
2. در **تنظیمات → پراکسی**، پراکسی تلگرام را روشن و آدرس را وارد کنید.
3. ذخیره کنید و در **[مرکز کنترل فروش](Sales-Control-Center)** دکمهٔ «ارسال پیام تست» را بزنید تا اتصال تلگرام تأیید شود.
4. اگر اتصال به پنل‌ها هم از ایران بسته است، پراکسی پنل‌ها را هم روشن کنید (می‌تواند همان آدرس باشد) و یک سرویس آزمایشی بسازید.

## 🩺 رفع مشکل
- **پیام تست نمی‌رود:** آدرس/پورت/scheme را بررسی کنید؛ `socks5h` را امتحان کنید.
- **پنل وصل نمی‌شود:** پراکسی پنل‌ها را روشن کنید و دسترسی پراکسی به IP پنل را چک کنید.
- پراکسی روی **تمام** تماس‌های cURL تلگرام/پنل اعمال می‌شود؛ پس از تغییر، نیازی به ری‌استارت نیست (در درخواست بعدی خوانده می‌شود).

---

<a name="-english"></a>
## 🇬🇧 English — Proxy setup (Iran hosts)

For admins hosting in **Iran** without direct access to Telegram or foreign panels, Faoxima supports **outbound proxies** — **separately** for Telegram and for panel connections.

### Where
Admin panel → **Settings (settings.php)** → **“Proxy (for Iran hosts)”**. Stored in the `setting` table (`proxy_telegram_status/url`, `proxy_panel_status/url`).

### Fields
- Use proxy for Telegram (on/off) + Telegram proxy URL
- Use proxy for panels (on/off) + Panels proxy URL (can be the same)

### URL format
```
scheme://[user:pass@]host:port
```
Schemes: `http, socks4, socks4a, socks5, socks5h`. No scheme ⇒ `http`. Use **`socks5h`** to resolve DNS on the proxy side (recommended from Iran).

Examples: `socks5h://1.2.3.4:1080`, `socks5h://user:pass@1.2.3.4:1080`, `http://10.0.0.5:8080`.

### Steps
1. Get a healthy non‑Iran proxy (SOCKS5 recommended).
2. Settings → Proxy → enable Telegram proxy, enter URL, Save.
3. Use the **[Sales Control Center](Sales-Control-Center)** “send test message” to confirm Telegram connectivity.
4. If panels are also blocked, enable the panels proxy and create a test service.

The proxy applies to all Telegram/panel cURL calls and is read on the next request (no restart needed).
