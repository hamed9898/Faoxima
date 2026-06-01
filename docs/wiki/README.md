# 📚 Faoxima Wiki sources

> **🇮🇷 فارسی** | [🇬🇧 English](#english)

این پوشه نسخهٔ نسخه‌بندی‌شدهٔ (version‑controlled) محتوای **ویکی گیتهاب** سورس است. صفحات با همان نام‌گذاری ویکی گیتهاب نوشته شده‌اند تا مستقیماً قابل انتشار باشند.

## 📄 صفحات
- `Home.md` — صفحهٔ اصلی + فهرست
- `_Sidebar.md` / `_Footer.md` — منوی کناری و فوتر ویکی
- `Installation-Server.md` / `Installation-Host.md` — نصب سرور و هاست
- `Configuration.md` — تنظیمات `config.php`
- `Payment-Gateways.md` — درگاه‌های پرداخت
- `Proxy-Setup.md` — پراکسی برای هاست ایران
- `3x-ui-Compatibility.md` — سازگاری 3x-ui
- `Admin-Panel.md` / `Sales-Control-Center.md` — پنل مدیر و مرکز کنترل فروش
- `Reseller-System.md` / `Reseller-Bot.md` — سیستم نمایندگان و ربات نماینده
- `Subscription-and-QR.md` — صفحهٔ سابسکریپشن و QR
- `Database-and-Backup.md` — دیتابیس، بکاپ و انتقال
- `FAQ-Troubleshooting.md` — عیب‌یابی

## 🚀 انتشار روی ویکی گیتهاب
ویکی گیتهاب اجازه نمی‌دهد **اولین صفحه** از طریق git ساخته شود. یک‌بار این کار را انجام دهید:
1. به آدرس `https://github.com/hamed9898/Faoxima/wiki` بروید و روی **«Create the first page»** کلیک کنید، چیزی بنویسید و Save کنید.
2. سپس از ریشهٔ پروژه اجرا کنید:
   ```bash
   bash docs/wiki/publish-wiki.sh
   ```
   این اسکریپت همهٔ صفحات این پوشه را در ویکی کپی و push می‌کند.

> اگر فورک/مخزن شما نام دیگری دارد: `bash docs/wiki/publish-wiki.sh <owner>/<repo>`

---

<a name="english"></a>
## 🇬🇧 English

This folder is the **version‑controlled source** of the project's **GitHub Wiki**. Pages use GitHub‑wiki naming so they publish as‑is.

### Publish to the GitHub Wiki
GitHub won't let you create the **first** wiki page over git. Do it once:
1. Open `https://github.com/hamed9898/Faoxima/wiki`, click **“Create the first page”**, type anything, Save.
2. From the repo root run:
   ```bash
   bash docs/wiki/publish-wiki.sh
   ```
   It copies every page here into the wiki repo and pushes it. For a different repo: `bash docs/wiki/publish-wiki.sh <owner>/<repo>`.
