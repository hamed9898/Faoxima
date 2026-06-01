# 🩺 عیب‌یابی و سؤالات متداول

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

## ❓ ربات پاسخ نمی‌دهد
1. **وب‌هوک** را بررسی کنید: [مرکز کنترل فروش](Sales-Control-Center) → `getWebhookInfo` (آدرس فعلی، آپدیت‌های در صف، آخرین خطا).
2. وب‌هوک را مجدداً ثبت کنید یا یک‌بار `https://<domain>/index.php` را باز کنید.
3. **HTTPS معتبر** داشته باشید (تلگرام HTTP را نمی‌پذیرد).
4. اگر هاست ایران است → **[پراکسی تلگرام](Proxy-Setup)** را روشن کنید و «پیام تست» بزنید.
5. صحت `$APIKEY` و `$adminnumber` (عددی) در `config.php` را چک کنید.

## ❓ خطای اتصال به دیتابیس
- مقادیر `$dbname/$usernamedb/$passworddb` را در `config.php` بررسی کنید.
- در لاگ سرور پیام `config.php ... connection failed` را ببینید.
- مطمئن شوید کاربر MySQL به دیتابیس دسترسی دارد.

## ❓ پنل (3x-ui/مرزبان) وصل نمی‌شود
- URL کامل با **web base path** و یوزر/رمز را بررسی کنید (رجوع: [3x-ui](3x-ui-Compatibility)).
- از ایران؟ **پراکسی پنل‌ها** را روشن کنید.

## ❓ پرداخت انجام شد ولی سرویس/شارژ اعمال نشد
- آدرس **کال‌بک** درگاه و HTTPS را بررسی کنید.
- در سیستم نمایندگان، شارژ کیف‌پول **اتمیک و دقیقاً‌یک‌بار** است؛ اگر کال‌بک شکست بخورد ردیف `pending` می‌ماند و دفعهٔ بعد دقیقاً یک‌بار اعمال می‌شود.
- لاگ خطاهای کسر کیف‌پول را برای تطبیق دستی ببینید.

## ❓ خرید ترکیبی (کیف‌پول + کارت) درست کسر می‌شود؟
بله. باگ قدیمی که موجودی اولیهٔ کیف‌پول کسر نمی‌شد رفع شده؛ حالا فقط **سهم کیف‌پول = قیمت فاکتور − مبلغ پرداختی** به‌صورت **اتمیک و race-safe** کسر می‌شود.

## ❓ بعد از آپدیت چیزی خراب شد
- دیتابیس **خودترمیم** است؛ معمولاً کافی است یک‌بار `index.php` را باز کنید.
- روی سرور: `faoxima` → **Update**. روی هاست: `config.php` را درست بازگردانید و `installer` را حذف کنید.

## ❓ امنیت — چه چیزهایی را رعایت کنم؟
- پوشهٔ **`installer`** را حذف کنید. 🔒
- **فیلتر IP** ورود (`setting.iplogin`) را برای پنل فعال کنید.
- فرم‌های پنل مدیر/نماینده با **CSRF** محافظت می‌شوند؛ از HTTPS معتبر استفاده کنید.

## 🐛 باگ‌های مالی رفع‌شده (خلاصه)
| باگ | وضعیت |
|-----|-------|
| عدم کسر موجودی در خرید ترکیبی (ربات اصلی) | ✅ رفع شد (اتمیک) |
| گم‌شدن پول در شارژ کیف‌پول نماینده/مشتری | ✅ رفع شد (بازگشت به pending) |
| گم‌شدن مبلغ در شکاف کسر‑سپس‑ثبت برداشت | ✅ رفع شد (بازگشت وجه) |
| بازگشت مضاعف وجه در ردِ برداشت | ✅ رفع شد (اتمیک، rowCount==1) |
| پاک‌شدن نام مشتری روی کال‌بک پرداخت | ✅ رفع شد |

---

<a name="-english"></a>
## 🇬🇧 English — FAQ & troubleshooting

### Bot not responding
Check the webhook in the [Sales Control Center](Sales-Control-Center) (`getWebhookInfo`); re‑set it or open `https://<domain>/index.php` once; ensure valid **HTTPS**; on Iran hosts enable the **[Telegram proxy](Proxy-Setup)** and use “send test message”; verify `$APIKEY` and numeric `$adminnumber` in `config.php`.

### DB connection error
Verify `$dbname/$usernamedb/$passworddb`; check server log for `config.php ... connection failed`; ensure the MySQL user can access the DB.

### Panel won’t connect (3x-ui/Marzban)
Verify the full URL with **web base path** + credentials (see [3x-ui](3x-ui-Compatibility)); from Iran, enable the **panels proxy**.

### Paid but no service/credit
Check the gateway **callback** URL + HTTPS. Reseller wallet credits are **atomic & exactly‑once** — a failed callback leaves the row `pending` and credits once next time. Check logs for debit errors.

### Mixed payment (wallet + card) deducted correctly?
Yes — the old bug (initial wallet not deducted) is fixed; only the **wallet share = invoice price − amount paid** is deducted, **atomically & race‑safe**.

### Something broke after update
The DB self‑heals; usually opening `index.php` once is enough. Server: `faoxima` → **Update**. Host: restore `config.php` correctly and delete `installer`.

### Security checklist
Delete **`installer`** 🔒, enable login **IP filter** (`setting.iplogin`), keep admin/reseller forms **CSRF**‑protected, use valid HTTPS.

### Fixed financial bugs (summary)
Mixed‑payment wallet deduction ✅ · reseller/customer top‑up money loss ✅ · withdrawal debit‑then‑record gap ✅ · double refund on rejection ✅ · customer name wiped on callback ✅.
