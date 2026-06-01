# 🛠️ پنل مدیریت اصلی

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

پنل وب مدیر در مسیر `/panel/` قرار دارد. ورود از `panel/login.php` با کاربر/رمز ادمین انجام می‌شود و نشست به‌صورت امن (regenerate id) مدیریت می‌شود.

## 🔐 ورود
```
https://<domain>/panel/
```
- در صورت فعال‌بودن **فیلتر IP** (از `setting.iplogin`)، فقط IPهای مجاز اجازهٔ ورود دارند.

## 🧭 بخش‌های اصلی منو
| صفحه | کاربرد |
|------|--------|
| **داشبورد** (`index.php`) | نمای کلی + نمودارها |
| **مرکز کنترل فروش** (`sales_control.php`) | KPIها، کلیدهای کنترل فروش، اتصال ربات اصلی — رجوع: [اینجا](Sales-Control-Center) |
| **محصولات** (`product.php`/`productedit.php`) | تعریف پلن‌ها، قیمت، حجم/زمان، پنل مقصد، «قابل فروش به نماینده» |
| **پنل‌ها/سرورها** (`panels.php`) | افزودن/مدیریت پنل‌های VPN (مرزبان، 3x-ui و …) |
| **مالی** (`finance.php`) | درگاه‌های پرداخت + متن‌های پرداخت — رجوع: [درگاه‌ها](Payment-Gateways) |
| **تنظیمات** (`settings.php`) | تنظیمات عمومی + **پراکسی** + فیلتر IP — رجوع: [پراکسی](Proxy-Setup) |
| **تنظیمات فروشگاه** (`shopsettings.php`) | کلیدهای رفتاری ربات (خرید مستقیم، نمایش قیمت و …) |
| **نمایندگان** (`resellers.php`) | مدیریت کامل نمایندگان — رجوع: [سیستم نمایندگان](Reseller-System) |
| **ارسال همگانی** (`broadcast.php`) | پیام انبوه به کاربران |
| **اپ/لینک‌ها** (`applinks.php`) | لینک اپلیکیشن‌ها و راهنمای اتصال |

## 🛒 جریان فروش (ربات اصلی)
1. کاربر در ربات محصول را انتخاب می‌کند.
2. فاکتور ساخته می‌شود؛ پرداخت از کیف‌پول و/یا درگاه.
3. پس از تأیید پرداخت، سرویس از پنل مقصد ساخته و کانفیگ/سابسکریپشن تحویل می‌شود.
4. مدیر از **مرکز کنترل فروش** می‌تواند رفتار فروش را لحظه‌ای تغییر دهد.

## 🧷 امنیت
- همهٔ فرم‌های حساس پنل با **CSRF** محافظت می‌شوند.
- پس از نصب، `installer` را حذف کنید و فیلتر IP ادمین را در نظر بگیرید.

---

<a name="-english"></a>
## 🇬🇧 English — Admin panel

Web admin lives at `/panel/`; log in via `panel/login.php`. Sessions are hardened (id regeneration). If **IP filtering** (`setting.iplogin`) is on, only allowed IPs can log in.

### Main sections
- **Dashboard** (`index.php`) — overview + charts
- **Sales Control Center** (`sales_control.php`) — KPIs, sales toggles, main‑bot connection → [here](Sales-Control-Center)
- **Products** (`product.php`/`productedit.php`) — plans, price, volume/time, target panel, “sellable to reseller”
- **Panels/Servers** (`panels.php`) — manage VPN panels (Marzban, 3x-ui…)
- **Finance** (`finance.php`) — gateways + payment texts → [Gateways](Payment-Gateways)
- **Settings** (`settings.php`) — general + **proxy** + IP filter → [Proxy](Proxy-Setup)
- **Shop settings** (`shopsettings.php`) — bot behavior flags
- **Resellers** (`resellers.php`) → [Reseller system](Reseller-System)
- **Broadcast** (`broadcast.php`) — mass messaging
- **App/Links** (`applinks.php`)

### Sales flow (main bot)
User picks a product → invoice → pay by wallet and/or gateway → on confirmation, service is provisioned and the config/subscription is delivered. The admin can change sales behavior live from the **Sales Control Center**.

### Security
Sensitive forms are **CSRF**‑protected; delete `installer` post‑install and consider admin IP filtering.
