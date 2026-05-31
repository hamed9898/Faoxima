<div align="center">

# 🤖 Faoxima — ربات و پنل فروش VPN

### نسخه‌ی ریفکتورشده و حرفه‌ای‌سازی‌شده‌ی ربات میرزا

**A complete VPN sales & management suite: Telegram bot · Mini-App · Admin panel · Reseller panel & bots**

[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Required-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Version](https://img.shields.io/badge/version-v0.0.2-success)](https://github.com/hamed9898/Faoxima)
[![License](https://img.shields.io/badge/license-Open%20Source-blue)](#-license--مجوز)

⭐ اگر این پروژه برایتان مفید بود، لطفاً **Star** بدهید!
⭐ If you find this project useful, please give it a **Star**!

</div>

---

<div align="center">

### 🌐 Language / زبان

**[🇮🇷 فارسی](#-فارسی)** &nbsp;•&nbsp; **[🇬🇧 English](#-english)**

</div>

---

<a name="-فارسی"></a>

# 🇮🇷 فارسی

## 📖 معرفی

**فاکسیما** یک ربات تلگرامی کامل برای **فروش و مدیریت اشتراک VPN** است که با PHP و MySQL نوشته شده و بازطراحیِ کاملِ ربات میرزا محسوب می‌شود. این سامانه شامل ربات تلگرام، مینی‌اپ تلگرام، پنل وب مدیریت، **سیستم نمایندگان با ربات اختصاصی هر نماینده**، و نصب‌کننده‌ی خودکار سرور است.

> 🧩 معماری: کد جدید در مسیر `re/rx/*` به‌صورت ماژولار روی هسته‌ی مونولیت قدیمی سوار شده و از طریق `index.php` به وب‌هوک تلگرام پاسخ می‌دهد.

---

## ✨ قابلیت‌ها

### 🛒 ربات و فروش اصلی
- 💳 درگاه‌های پرداخت: **زرین‌پال، آقای‌پرداخت، ایران‌پی، کارت‌به‌کارت** و **کریپتو** (NowPayments، Plisio، TRON/ترون، کریپتوی آفلاین با تأیید هش)
- 👛 کیف پول کاربر + **پرداخت ترکیبی** (موجودی کیف پول + مابه‌التفاوت با کارت/درگاه/کریپتو)
- 📦 انبارداری پیشرفته، فروش دستی عمده و پنل اضطراری
- 🎨 رنگ‌بندی دکمه‌ها، ایموجی پرمیوم، کارت رنگی مشاهده‌ی حجم
- 📱 مینی‌اپ تلگرام بازطراحی‌شده و مدرن

### 🔌 سازگاری با پنل‌ها
پشتیبانی از: **Marzban، Marzneshin، 3x-ui (صنایی)، x-ui (علیرضا)، Hiddify، S-UI، WG-Dashboard، MikroTik، IBSng، Guard** و فروش دستی/انبار.
- ✅ **سازگاری کامل با 3x-ui صنایی نسخه v3.1.0** (احراز هویت توکن‌محور API)

### 👥 سیستم نمایندگان (Reseller)
- 🏪 ساخت نماینده از پنل مدیر با کنترل کامل (محصولات مجاز، سقف اختیاری کیف پول/ساخت)
- 💰 کیف پول نماینده + شارژ با درگاه‌های موجود (واریز اتمیک و دقیقاً‌یک‌بار)
- 🛠️ ساخت/مدیریت سرویس فقط از محصولات مجاز؛ **اول ساخت روی پنل، بعد کسر کیف پول**
- 📊 حسابداری و گزارش کامل با دفتر کل (`reseller_ledger`)
- 💵 درخواست برداشت **USDT / TRON** با حداقلِ تعیین‌شده توسط مدیر
- 🖼️ صفحه‌ی زیبای سابسکریپشن + **QR Code**

### 🤖 ربات اختصاصی هر نماینده
- هر نماینده توکن ربات خود را وارد می‌کند (تک‌هاست، چند ربات، تفکیک با توکن + زیرساخت چند-هاست)
- 👛 شارژ کیف پول مشتری از داخل ربات + خرید مستقیم سرویس
- 🔁 بازگشت خودکار وجه در صورت شکست ساخت سرویس

### 🌍 پشتیبانی پراکسی (مخصوص هاست ایران)
- 🔗 تنظیم پراکسی برای **اتصال به تلگرام** و **اتصال به پنل‌ها** از داخل پنل مدیریت
- پشتیبانی از انواع **HTTP، SOCKS4، SOCKS5**

### 💎 صحت مالی (Financial Integrity)
- کسر و واریز **اتمیک و دقیقاً‌یک‌بار** (race-safe) در همه‌ی مسیرهای مالی
- رفع باگ پرداخت ترکیبی: کسر صحیح سهم کیف پول = `قیمت فاکتور − مبلغ پرداخت‌شده`

---

## ⚙️ پیش‌نیازها

| مورد | حداقل |
|------|-------|
| 🐘 PHP | 8.2 یا 8.3 |
| 🗄️ MySQL / MariaDB | الزامی |
| 🌐 وب‌سرور | Apache 2 / Nginx + HTTPS (SSL) |
| ⏰ Cron Job | **اجباری** |
| 🧩 افزونه‌های PHP | `curl`, `pdo_mysql`, `mbstring`, `json`, `gd` |

---

## 🚀 نصب

### 🖥️ روش ۱ — نصب خودکار روی سرور (Ubuntu 22)

```bash
curl -L -o /root/install.sh https://raw.githubusercontent.com/hamed9898/Faoxima/main/install.sh && bash /root/install.sh
```

این اسکریپت Apache 2 + PHP 8.2 + MySQL + phpMyAdmin را نصب و ربات را راه‌اندازی می‌کند. پس از نصب با دستور `faoxima` می‌توانید ربات را مدیریت (آپدیت/حذف/تنظیم) کنید.

📄 راهنمای کامل سرور: [server.md](server.md)

### 🌐 روش ۲ — نصب روی هاست (cPanel / aaPanel)

📄 راهنمای کامل هاست: [host.md](host.md)

1. سورس را در مسیر public هاست آپلود کنید.
2. دیتابیس MySQL بسازید و اطلاعات آن را در `config.php` وارد کنید.
3. آدرس `https://YOUR_DOMAIN/table.php?token=ADMIN_TOKEN` را یک‌بار باز کنید تا جداول ساخته شوند.
4. وب‌هوک ربات را روی `https://YOUR_DOMAIN/index.php` تنظیم کنید.
5. Cron Job را برای اجرای `cron/cron.php` (هر دقیقه) فعال کنید.

> 🛡️ دیتابیس خودترمیم است؛ ستون‌ها/جداول گم‌شده در زمان اجرا به‌صورت خودکار ساخته می‌شوند.

---

## 🔧 تنظیمات کلیدی

| بخش | محل |
|------|-----|
| 🔑 توکن ربات و اطلاعات دیتابیس | `config.php` |
| ⚙️ تنظیمات عمومی، درگاه‌ها، پراکسی | پنل مدیریت → تنظیمات |
| 👥 مدیریت نمایندگان | پنل مدیریت → `resellers.php` |
| 🏪 ورود نماینده | `https://YOUR_DOMAIN/panel/reseller/login.php` |
| 🤖 وب‌هوک ربات نماینده | `https://YOUR_DOMAIN/panel/reseller/bot.php?secret=<bot_secret>` (به‌صورت خودکار از پنل نماینده ست می‌شود) |

### 🌍 فعال‌سازی پراکسی (هاست ایران)
از **پنل مدیریت → تنظیمات** گزینه‌های زیر را تنظیم کنید:
- `proxy_telegram_status` + `proxy_telegram_url` — برای اتصال به API تلگرام
- `proxy_panel_status` + `proxy_panel_url` — برای اتصال به پنل‌های VPN

فرمت آدرس پراکسی: `socks5://user:pass@host:port` یا `http://host:port`

---

## 🗂️ ساختار پروژه

```
Faoxima/
├── index.php              # ورودی وب‌هوک تلگرام
├── config.php             # تنظیمات دیتابیس و توکن
├── table.php              # ساخت/مهاجرت جداول
├── botapi.php             # هسته‌ی API ربات
├── panels.php             # کلاس ManagePanel (اتصال به انواع پنل)
├── proxy.php              # لایه‌ی پراکسی (تلگرام + پنل‌ها)
├── x-ui_single.php        # آداپتور 3x-ui صنایی
├── re/rx/                 # کد ماژولار جدید (user_flow، bootstrap، helpers)
├── panel/                 # پنل وب مدیریت
│   └── reseller/          # پنل نمایندگان + ربات‌های اختصاصی
├── api/                   # API مینی‌اپ تلگرام
├── app/                   # فرانت‌اند مینی‌اپ (SPA)
├── cron/ , cronbot/       # کرون‌ها (انقضا، اعلان، بکاپ، چک پرداخت و …)
└── install.sh             # نصب‌کننده‌ی سرور
```

---

## 🧪 سلامت مالی و تست
تمام مسیرهای مالی (ربات اصلی، پنل نماینده، ربات نماینده) برای کسر/واریز **اتمیک و دقیقاً‌یک‌بار** ممیزی شده‌اند. باگ پرداخت ترکیبی (عدم کسر موجودی اولیه‌ی کیف پول) برطرف شده است.

## 💸 Support / حمایت مالی
<a href="https://plisio.net/donate/xNyn7NLZ" target="_blank"><img src="https://plisio.net/img/donate/donate_light_icons_color.png" alt="Donate Crypto on Plisio" width="240" height="80" /></a>
---

<div align="center">

[⬆️ بازگشت به بالا](#-faoxima--ربات-و-پنل-فروش-vpn)

</div>

---

<a name="-english"></a>

# 🇬🇧 English

## 📖 Overview

**Faoxima** is a complete Telegram bot for **selling and managing VPN subscriptions**, built with PHP & MySQL — a full refactor of the Mirza bot. It bundles a Telegram bot, a Telegram Mini-App, a web admin panel, a **multi-tenant reseller system with a dedicated Telegram bot per reseller**, and an automated server installer.

> 🧩 Architecture: the new modular code lives under `re/rx/*` and runs on top of the legacy monolith core, responding to the Telegram webhook through `index.php`.

---

## ✨ Features

### 🛒 Main bot & sales
- 💳 Payment gateways: **Zarinpal, AqayePardakht, IranPay, card-to-card** and **crypto** (NowPayments, Plisio, TRON, offline crypto with hash verification)
- 👛 User wallet + **mixed payment** (wallet balance + remainder via card/gateway/crypto)
- 📦 Advanced inventory, bulk manual sales, emergency panel
- 🎨 Button coloring, premium emoji, colorful usage cards
- 📱 Redesigned, modern Telegram Mini-App

### 🔌 Panel compatibility
Supports: **Marzban, Marzneshin, 3x-ui (Sanaei), x-ui (Alireza), Hiddify, S-UI, WG-Dashboard, MikroTik, IBSng, Guard**, plus manual/inventory sales.
- ✅ **Full compatibility with MHSanaei 3x-ui v3.1.0** (API-token authentication)

### 👥 Reseller system
- 🏪 Create resellers from the admin panel with full control (allowed products, optional wallet/creation caps)
- 💰 Reseller wallet + top-up via existing gateways (atomic, exactly-once credit)
- 🛠️ Create/manage services only from allowed products; **provision on panel first, debit wallet second**
- 📊 Full accounting & reporting via a ledger (`reseller_ledger`)
- 💵 **USDT / TRON** withdrawal requests with an admin-defined minimum
- 🖼️ Beautiful subscription page + **QR code**

### 🤖 Per-reseller Telegram bot
- Each reseller registers their own bot token (single host, many bots, token-based routing + multi-host groundwork)
- 👛 Customer wallet top-up inside the bot + direct service purchase
- 🔁 Automatic refund if provisioning fails

### 🌍 Proxy support (for Iran-based hosts)
- 🔗 Configure a proxy for **Telegram connectivity** and **panel connectivity** from the admin panel
- Supports **HTTP, SOCKS4, SOCKS5**

### 💎 Financial integrity
- **Atomic, exactly-once** (race-safe) debits/credits across every money flow
- Fixed the mixed-payment bug: wallet share is now correctly debited as `invoice price − amount paid`

---

## ⚙️ Requirements

| Item | Minimum |
|------|---------|
| 🐘 PHP | 8.2 or 8.3 |
| 🗄️ MySQL / MariaDB | Required |
| 🌐 Web server | Apache 2 / Nginx + HTTPS (SSL) |
| ⏰ Cron job | **Mandatory** |
| 🧩 PHP extensions | `curl`, `pdo_mysql`, `mbstring`, `json`, `gd` |

---

## 🚀 Installation

### 🖥️ Option 1 — Automated server install (Ubuntu 22)

```bash
curl -L -o /root/install.sh https://raw.githubusercontent.com/hamed9898/Faoxima/main/install.sh && bash /root/install.sh
```

This installs Apache 2 + PHP 8.2 + MySQL + phpMyAdmin and sets up the bot. After install, use the `faoxima` command to manage it (update/remove/configure).

📄 Full server guide: [server.md](server.md)

### 🌐 Option 2 — Shared hosting (cPanel / aaPanel)

📄 Full hosting guide: [host.md](host.md)

1. Upload the source to your host's public directory.
2. Create a MySQL database and enter its credentials in `config.php`.
3. Open `https://YOUR_DOMAIN/table.php?token=ADMIN_TOKEN` once to build the tables.
4. Set the bot webhook to `https://YOUR_DOMAIN/index.php`.
5. Enable a cron job running `cron/cron.php` (every minute).

> 🛡️ The database is self-healing — missing columns/tables are created automatically at runtime.

---

## 🔧 Key configuration

| Area | Location |
|------|----------|
| 🔑 Bot token & DB credentials | `config.php` |
| ⚙️ General settings, gateways, proxy | Admin panel → Settings |
| 👥 Reseller management | Admin panel → `resellers.php` |
| 🏪 Reseller login | `https://YOUR_DOMAIN/panel/reseller/login.php` |
| 🤖 Reseller bot webhook | `https://YOUR_DOMAIN/panel/reseller/bot.php?secret=<bot_secret>` (set automatically from the reseller panel) |

### 🌍 Enabling the proxy (Iran hosts)
From **Admin panel → Settings**, configure:
- `proxy_telegram_status` + `proxy_telegram_url` — for the Telegram API
- `proxy_panel_status` + `proxy_panel_url` — for the VPN panels

Proxy URL format: `socks5://user:pass@host:port` or `http://host:port`

---

## 🗂️ Project structure

```
Faoxima/
├── index.php              # Telegram webhook entry point
├── config.php             # DB credentials & token
├── table.php              # Table creation / migration
├── botapi.php             # Bot API core
├── panels.php             # ManagePanel class (panel adapters)
├── proxy.php              # Proxy layer (Telegram + panels)
├── x-ui_single.php        # 3x-ui (Sanaei) adapter
├── re/rx/                 # New modular code (user_flow, bootstrap, helpers)
├── panel/                 # Web admin panel
│   └── reseller/          # Reseller panel + per-reseller bots
├── api/                   # Telegram Mini-App API
├── app/                   # Mini-App frontend (SPA)
├── cron/ , cronbot/       # Cron jobs (expiry, notifications, backup, payment checks…)
└── install.sh             # Server installer
```

---

## 🧪 Financial health & testing
All money flows (main bot, reseller panel, reseller bot) have been audited for **atomic, exactly-once** debits/credits. The mixed-payment bug (initial wallet balance not deducted) has been fixed.

---

## 📜 License / مجوز
Open source. Refactored and maintained by the community.
متن‌باز — بازطراحی و نگهداری توسط جامعه‌ی کاربری.

## 💸 Support / حمایت مالی
<a href="https://plisio.net/donate/xNyn7NLZ" target="_blank"><img src="https://plisio.net/img/donate/donate_light_icons_color.png" alt="Donate Crypto on Plisio" width="240" height="80" /></a>

<div align="center">

[⬆️ Back to top](#-faoxima--ربات-و-پنل-فروش-vpn)

Made with ❤️ for the VPN community

</div>
