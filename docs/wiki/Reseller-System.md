# 🧑‍💼 سیستم نمایندگان (پنل چند‌مستأجری)

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

سیستم نمایندگان به مدیر اجازه می‌دهد برای فروشندگان دیگر **پنل اختصاصی** بسازد. هر نماینده کیف‌پول خودش را شارژ می‌کند، از محصولات مجاز سرویس می‌سازد و می‌فروشد، حسابداری/گزارش دارد و می‌تواند درآمدش را با USDT/TRON برداشت کند. **مدل اعتباری وجود ندارد** — همه‌چیز پیش‌پرداخت از کیف‌پول است.

## 🏗️ ساخت نماینده (از پنل مدیر)
پنل مدیر → **نمایندگان** (`resellers.php`) → افزودن نماینده:
- **یوزرنیم و رمز** (رمز هش می‌شود؛ ورودهای قدیمیِ plaintext هم سازگارند)
- **نام، تلفن، telegram_id**
- **توکن ربات (اختیاری)** — برای ربات اختصاصی نماینده (رجوع: [ربات نماینده](Reseller-Bot))
- **موجودی اولیه** (اختیاری)
- **سقف‌های اختیاری:** `limit_balance` (سقف کیف‌پول)، `limit_services` (سقف تعداد ساخت)
- **محصولات مجاز** (`allowed_products`) — فقط همین‌ها قابل فروش‌اند
- **حداقل برداشت** (`min_withdraw`)

> 🔓 به‌صورت پیش‌فرض **سقفی وجود ندارد**؛ سقف‌ها فقط در صورت نیاز توسط مدیر اعمال می‌شوند.

## 🔑 ورود نماینده
نماینده از این آدرس وارد پنل خودش می‌شود:
```
https://<domain>/panel/reseller/login.php
```
- فیلتر IP از `setting.iplogin` به‌ارث می‌رسد.

## 💰 کیف‌پول و شارژ
- نماینده کیف‌پولش را با **درگاه‌های موجود** (زرین‌پال + آقای‌پرداخت) شارژ می‌کند.
- واریزها **اتمیک و دقیقاً‌یک‌بار** هستند (idempotent): اگر کال‌بک شکست بخورد، ردیف به `pending` برمی‌گردد تا دفعهٔ بعد دقیقاً یک‌بار شارژ شود — هیچ پولی گم یا دوبار شارژ نمی‌شود.

## 🧱 ساخت و مدیریت سرویس
- نماینده فقط از **محصولات مجاز** سرویس می‌سازد.
- ترتیب امن: **اول** سرویس روی پنل ساخته می‌شود، **سپس** هزینه از کیف‌پول کسر می‌شود (اگر ساخت ناموفق باشد، کسری انجام نمی‌شود).
- مدیریت سرویس‌ها: مشاهده، تمدید، حجم/زمان، وضعیت.

## 📑 حسابداری و گزارش
- دفترکل کامل در جدول `reseller_ledger` (هر واریز/کسر/برداشت ثبت می‌شود).
- صفحهٔ **گزارش‌ها** (`reports.php`): فروش، موجودی، تراکنش‌ها.

## 🪙 برداشت درآمد (USDT / TRON)
- نماینده با رعایت **حداقلِ تعیین‌شده توسط مدیر** درخواست برداشت ثبت می‌کند (`withdraw.php`).
- مبلغ هنگام ثبت درخواست به‌صورت **اتمیک** از کیف‌پول کسر می‌شود؛ اگر ثبت خطا بدهد، وجه بازمی‌گردد.
- مدیر در `resellers.php`:
  - **تأیید** با وارد‌کردن **txid** → برداشت نهایی می‌شود.
  - **رد** → وجه به‌صورت اتمیک و فقط یک‌بار (`rowCount==1`) بازگردانده می‌شود (ضد دابل‌کلیک/دابل‌ریفاند).

## 🛡️ کنترل‌های مدیر روی نماینده (resellers.php)
CRUD کامل + تنظیم موجودی + سقف‌ها + محصولات مجاز + تأیید/رد برداشت — همه با **CSRF** و بررسی مالکیت (ضد IDOR).

## 🌐 پشتیبانی چند‌هاست
ستون `panel_host_id` پایهٔ استفاده از منابعِ چند هاست را در آینده فراهم می‌کند (هر نماینده/پنل می‌تواند به هاست متفاوت اشاره کند).

---

<a name="-english"></a>
## 🇬🇧 English — Reseller system (multi‑tenant)

Lets the admin create dedicated panels for other sellers. Each reseller tops up their own wallet, provisions services from allowed products, has accounting/reports, and can withdraw earnings via USDT/TRON. **No credit model** — everything is prepaid from the wallet.

### Create a reseller (admin)
Admin → **Resellers** (`resellers.php`) → add: username/password (hashed; legacy plaintext compatible), name/phone/telegram_id, optional **bot token** (→ [Reseller bot](Reseller-Bot)), initial balance, optional caps `limit_balance`/`limit_services`, `allowed_products`, `min_withdraw`. By default there are **no caps**.

### Reseller login
`https://<domain>/panel/reseller/login.php` (IP filter inherited from `setting.iplogin`).

### Wallet & top‑up
Top up via existing gateways (ZarinPal + AqayePardakht). Credits are **atomic & exactly‑once**: a failed callback reverts the row to `pending` so the next one credits exactly once — no lost or double credit.

### Service create/manage
Only from **allowed products**. Safe order: create on the panel **first**, then debit the wallet (no debit if creation fails). Manage: view, renew, volume/time, status.

### Accounting & reports
Full ledger in `reseller_ledger`; **Reports** page (`reports.php`) for sales, balance, transactions.

### Withdrawals (USDT/TRON)
Reseller requests above the admin‑set minimum (`withdraw.php`); the amount is debited atomically on request (refunded if recording fails). Admin **approves** with a **txid**, or **rejects** with an atomic, single‑shot refund (`rowCount==1`, double‑click safe).

### Admin controls (resellers.php)
Full CRUD + balance set + caps + allowed products + withdrawal approvals — all **CSRF**‑protected with ownership/IDOR checks.

### Multi‑host
`panel_host_id` lays the groundwork for spreading load across multiple hosts.
