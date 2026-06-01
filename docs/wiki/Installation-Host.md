# 🌐 نصب روی هاست (cPanel / aaPanel)

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

اگر سرور اختصاصی ندارید، می‌توانید سورس را روی هاست اشتراکی **cPanel** یا **aaPanel** نصب کنید.

## ✅ پیش‌نیازها
- هاست با **PHP 8.2+** و **افزونه‌های**: `pdo_mysql`, `mysqli`, `curl`, `mbstring`, `gd`, `openssl`, `zip`, `json`.
- یک **دیتابیس MySQL** + کاربر (از بخش MySQL Databases هاست بسازید).
- دامنه با **SSL فعال** (تلگرام برای وب‌هوک نیازمند HTTPS است).

## 1️⃣ آپلود سورس
1. آخرین نسخهٔ ZIP را از Releases دانلود کنید:
   ```
   https://github.com/hamed9898/Faoxima/releases
   ```
2. ZIP را در `public_html` (یا زیر‌پوشهٔ دلخواه مثل `Faoxima`) آپلود و **Extract** کنید.

## 2️⃣ اجرای Installer
آدرس زیر را در مرورگر باز کنید (به‌جای `domain.ir` و مسیر، مقادیر خودتان را بگذارید):
```
https://domain.ir/Faoxima/installer
```
Installer جداول دیتابیس را می‌سازد و فایل `config.php` را پر می‌کند (دیتابیس، توکن ربات، Chat ID ادمین، دامنه و یوزرنیم ربات).

## 3️⃣ حذف اجباری پوشهٔ installer 🔒⚠️
پس از اتمام، **حتماً** پوشهٔ `installer` را از فایل‌منیجر هاست حذف کنید.

## 4️⃣ تنظیم وب‌هوک
اگر Installer وب‌هوک را ثبت نکرد، یک‌بار این آدرس را باز کنید:
```
https://domain.ir/Faoxima/index.php
```
یا از **مرکز کنترل فروش** (`/panel/sales_control.php`) دکمهٔ «ثبت/به‌روزرسانی وب‌هوک» را بزنید.

## 5️⃣ تأیید
- ربات را `/start` بزنید.
- پنل: `https://domain.ir/Faoxima/panel/`.

## 🔄 آپدیت روی هاست
1. ابتدا محتوای `config.php` فعلی را **کپی و ذخیره** کنید.
2. سورس قدیمی را کامل حذف و نسخهٔ جدید را آپلود کنید.
3. پوشهٔ `installer` را حذف کنید. 🔒
4. محتوای `config.php` ذخیره‌شده را در فایل جدید جایگزین کنید.
5. اگر کار نکرد، یک‌بار `https://domain.ir/Faoxima/index.php` را باز کنید.

> 💡 برای هاست‌های ایران که به تلگرام/پنل‌ها دسترسی ندارند، حتماً **[تنظیم پراکسی](Proxy-Setup)** را ببینید.

---

<a name="-english"></a>
## 🇬🇧 English — Shared host install (cPanel / aaPanel)

### ✅ Requirements
- PHP **8.2+** with `pdo_mysql, mysqli, curl, mbstring, gd, openssl, zip, json`.
- A MySQL database + user. A domain with **SSL enabled** (Telegram webhook needs HTTPS).

### 1️⃣ Upload
Download the latest ZIP from `https://github.com/hamed9898/Faoxima/releases`, upload to `public_html` (or a subfolder e.g. `Faoxima`) and extract.

### 2️⃣ Run the installer
Open `https://domain.ir/Faoxima/installer`. It creates the DB tables and fills `config.php` (DB creds, bot token, admin chat ID, domain, bot username).

### 3️⃣ Delete the `installer` folder (required) 🔒⚠️

### 4️⃣ Set the webhook
If not auto‑registered, open `https://domain.ir/Faoxima/index.php` once, or use the **Sales Control Center** “set/refresh webhook” button.

### 5️⃣ Verify
`/start` the bot; panel at `https://domain.ir/Faoxima/panel/`.

### 🔄 Updating
Copy your current `config.php` first, remove the old source, upload the new one, delete `installer`, restore `config.php`, then open `index.php` once if needed.

> 💡 For Iran‑hosted servers, see **[Proxy setup](Proxy-Setup)**.
