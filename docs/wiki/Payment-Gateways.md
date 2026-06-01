# 💳 درگاه‌های پرداخت

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

Faoxima از درگاه‌های ایرانی، کریپتو و کارت‌به‌کارت پشتیبانی می‌کند. تنظیمات از پنل مدیر → بخش **مالی (finance.php)** انجام می‌شود.

## 🏦 درگاه‌های پشتیبانی‌شده
| درگاه | نوع | نیازمندی |
|------|-----|---------|
| **زرین‌پال (ZarinPal)** | درگاه ایرانی | Merchant ID |
| **آقای‌پرداخت (AqayePardakht)** | درگاه ایرانی | Pin |
| **ایران‌پی / IranPay** | درگاه ایرانی | کلید API |
| **کارت‌به‌کارت** | دستی (تأیید ادمین) | شماره کارت + نام |
| **NowPayments** | کریپتو | API Key |
| **Plisio** | کریپتو | API Key |
| **TRON / Tatum (TRX/USDT)** | کریپتو آنچین | آدرس کیف‌پول / کلید |
| **کریپتوی آفلاین** | دستی با تأیید هش | آدرس + تأیید txid |

## ⚙️ نحوهٔ فعال‌سازی
1. پنل مدیر → **مالی**.
2. درگاه موردنظر را روشن کنید و کلید/مرچنت را وارد کنید.
   - فیلدهای حساس به‌صورت `••••XXXX` ماسک می‌شوند؛ برای تغییر مقدار جدید را تایپ کنید، خالی بماند تغییری اعمال نمی‌شود.
3. ذخیره کنید.

## 💰 کیف‌پول و پرداخت ترکیبی
- کاربر می‌تواند کیف‌پولش را شارژ کند و بعد خرید کند.
- در **خرید ترکیبی**، اگر موجودی کمتر از قیمت باشد، کاربر فقط **مابه‌التفاوت (قیمت − موجودی)** را از درگاه/کارت پرداخت می‌کند و باقی از کیف‌پول کسر می‌شود.
- ✅ این مسیر از نظر مالی **اتمیک و دقیقاً‌یک‌بار** اصلاح شده است (سهم کیف‌پول به‌صورت race-safe کسر می‌شود) — رجوع کنید به [عیب‌یابی](FAQ-Troubleshooting).

## 🔁 وب‌هوک/کال‌بک پرداخت
هر درگاه پس از پرداخت به آدرس کال‌بک سورس برمی‌گردد و سرویس به‌صورت خودکار تحویل می‌شود. مطمئن شوید دامنه/HTTPS درست تنظیم است تا کال‌بک‌ها برسند.

## 🪙 برداشت نماینده (USDT/TRON)
نمایندگان می‌توانند با رعایت **حداقلِ تعیین‌شده توسط مدیر** درخواست برداشت USDT/TRON ثبت کنند؛ مدیر با txid تأیید یا با بازگشت خودکار وجه رد می‌کند. جزئیات: **[سیستم نمایندگان](Reseller-System)**.

---

<a name="-english"></a>
## 🇬🇧 English — Payment gateways

Configured from Admin panel → **Finance (finance.php)**.

### Supported
ZarinPal (Merchant ID), AqayePardakht (Pin), IranPay (API key), Card‑to‑card (manual/admin approval), NowPayments, Plisio, TRON/Tatum (TRX/USDT on‑chain), and offline crypto with hash confirmation.

### Enable
Admin → Finance → toggle a gateway, enter its key/merchant (secrets are masked as `••••XXXX`; leave blank to keep), Save.

### Wallet & mixed payments
Users can top up a wallet and buy. In a **mixed payment**, when balance < price, the user pays only the **remainder (price − balance)** via gateway/card and the rest is deducted from the wallet. ✅ This flow is fixed to deduct the wallet share **atomically, exactly‑once** (race‑safe).

### Reseller withdrawals (USDT/TRON)
Resellers request USDT/TRON withdrawals above an admin‑set minimum; admin approves with a txid or rejects with automatic refund. See **[Reseller system](Reseller-System)**.
