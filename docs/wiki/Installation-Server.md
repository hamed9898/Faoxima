# 🚀 نصب روی سرور (اسکریپت خودکار)

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

اسکریپت رسمی `install.sh` یک استک کامل **Apache 2 + PHP 8.2 + MySQL + phpMyAdmin** را روی **Ubuntu/Debian** نصب می‌کند و در صورت وجود، در کنار پنل Marzban (MySQL داکر) هم کار می‌کند.

## ✅ پیش‌نیازها
- سرور **Ubuntu 20.04/22.04** یا **Debian 11/12** تازه.
- دسترسی **root**.
- یک **دامنه** که رکورد A آن به IP سرور اشاره کند (برای SSL/وب‌هوک).
- توکن ربات از [@BotFather](https://t.me/BotFather) و **عددی Chat ID** ادمین از [@userinfobot](https://t.me/userinfobot).

## 1️⃣ اجرای اسکریپت نصب
```bash
curl -L -o /root/install.sh https://raw.githubusercontent.com/hamed9898/Faoxima/main/install.sh && bash /root/install.sh
```
> اگر از مخزن اصلی استفاده می‌کنید، به‌جای `hamed9898` نام مخزن خود را بگذارید.

## 2️⃣ انتخاب گزینهٔ نصب
از منوی اسکریپت گزینهٔ **«1) Install / نصب»** را انتخاب کنید و اطلاعات خواسته‌شده را وارد کنید:
- **دامنه** (مثلاً `bot.example.com`)
- **توکن ربات** (`APIKEY`)
- **Chat ID ادمین** (`adminnumber`)
- **یوزرنیم ربات** (بدون `@`)
- اسکریپت به‌صورت خودکار دیتابیس، کاربر MySQL، گواهی SSL و وب‌هوک تلگرام را تنظیم می‌کند.

اطلاعات دیتابیس (root) در مسیر زیر ذخیره می‌شود:
```
/root/conffaoxima/dbrootfaoxima.txt
```

## 3️⃣ حذف اجباری پوشهٔ Installer 🔒
پس از پایان نصب حتماً Installer را حذف کنید (امنیتی و الزامی):
```bash
rm -r /var/www/html/faoxima/installer
```

## 4️⃣ تأیید نصب
- ربات تلگرام را `/start` بزنید؛ باید پاسخ دهد.
- پنل مدیر: `https://<دامنه>/panel/` → ورود با کاربر/رمز ادمین.
- وضعیت وب‌هوک را در **مرکز کنترل فروش** (`/panel/sales_control.php`) ببینید.

## 🧰 مدیریت بعد از نصب
اسکریپت یک میان‌بر سراسری می‌سازد؛ هر زمان این دستور را بزنید تا منوی مدیریت (آپدیت، حذف، بکاپ و …) باز شود:
```bash
faoxima
```

## 🩺 رفع مشکل سریع
اگر ربات بعد از نصب پاسخ نداد، یک‌بار آدرس وب‌هوک را در مرورگر باز کنید:
```
https://<دامنه>/index.php
```
سپس از منوی `faoxima` گزینهٔ **Update** را اجرا کنید. برای جزئیات بیشتر: **[عیب‌یابی](FAQ-Troubleshooting)**.

---

<a name="-english"></a>
## 🇬🇧 English — Server install (automated)

The official `install.sh` sets up a full **Apache 2 + PHP 8.2 + MySQL + phpMyAdmin** stack on **Ubuntu/Debian**, and coexists with an existing Marzban (Dockerized MySQL) panel.

### ✅ Requirements
- Fresh **Ubuntu 20.04/22.04** or **Debian 11/12**, **root** access.
- A **domain** with an A record pointing to the server IP (for SSL/webhook).
- Bot token from [@BotFather](https://t.me/BotFather) and your numeric admin **Chat ID** from [@userinfobot](https://t.me/userinfobot).

### 1️⃣ Run the installer
```bash
curl -L -o /root/install.sh https://raw.githubusercontent.com/hamed9898/Faoxima/main/install.sh && bash /root/install.sh
```

### 2️⃣ Choose “Install”
Pick option **1) Install** and provide: domain, bot token (`APIKEY`), admin chat ID (`adminnumber`), bot username. The script provisions the database, MySQL user, SSL certificate and Telegram webhook automatically. DB root credentials are saved to `/root/conffaoxima/dbrootfaoxima.txt`.

### 3️⃣ Remove the installer (required) 🔒
```bash
rm -r /var/www/html/faoxima/installer
```

### 4️⃣ Verify
- `/start` the bot — it should reply.
- Admin panel: `https://<domain>/panel/`.
- Check webhook health in the **Sales Control Center** (`/panel/sales_control.php`).

### 🧰 Manage later
Run `faoxima` anytime to open the management menu (update, remove, backup…).

### 🩺 Quick fix
If the bot is silent, open `https://<domain>/index.php` once, then run **Update** from the `faoxima` menu. See **[FAQ & troubleshooting](FAQ-Troubleshooting)**.
