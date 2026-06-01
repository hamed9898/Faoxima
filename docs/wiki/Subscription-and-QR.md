# 🔗 صفحه سابسکریپشن و QR

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

پس از ساخت سرویس، Faoxima یک **صفحهٔ سابسکریپشن زیبا** به همراه **QR code** در اختیار مشتری می‌گذارد تا اتصال ساده باشد.

## 🧷 ویژگی‌ها
- لینک سابسکریپشن با **توکن غیرقابل‌حدس** برای هر سرویس (امن در برابر شمارش/حدس).
- نمایش **QR code** سابسکریپشن برای اسکن سریع در اپ‌ها.
- نمایش اطلاعات سرویس: حجم/زمان باقی‌مانده، وضعیت، و راهنمای اتصال.

## 🧭 مسیرها
- پنل نماینده → **سرویس‌ها** → مشاهدهٔ سرویس → صفحهٔ سابسکریپشن (`panel/reseller/subscription.php`) و تصویر QR (`panel/reseller/qr.php`).
- در ربات‌ها، لینک سابسکریپشن + QR مستقیماً برای کاربر ارسال می‌شود.

## 📲 استفادهٔ مشتری
1. لینک سابسکریپشن را در اپ (v2rayNG، Streisand، Hiddify و …) وارد کنید یا QR را اسکن کنید.
2. کانفیگ‌ها به‌صورت خودکار به‌روزرسانی می‌شوند (بر اساس نوع پنل).

> 💡 برای کارکردن لینک‌ها از داخل ایران، دامنه/SSL باید درست باشد؛ و اگر سرور در ایران است، **[پراکسی پنل‌ها](Proxy-Setup)** برای ساخت سرویس لازم است (نه برای دسترسی مشتری).

---

<a name="-english"></a>
## 🇬🇧 English — Subscription page & QR

After a service is created, Faoxima provides a clean **subscription page** plus a **QR code** for easy connection.

### Features
- Subscription link with an **unguessable per‑service token** (enumeration‑safe).
- A **QR code** for quick scanning in apps.
- Service details: remaining volume/time, status, connection help.

### Where
Reseller panel → **Services** → view → subscription page (`panel/reseller/subscription.php`) and QR image (`panel/reseller/qr.php`). In bots, the subscription link + QR are sent directly to the user.

### Customer usage
Import the subscription link into an app (v2rayNG, Streisand, Hiddify…) or scan the QR; configs auto‑update per panel type.

> 💡 Links require a correct domain/SSL. If the server is in Iran, the **[panels proxy](Proxy-Setup)** is needed for service creation (not for customer access).
