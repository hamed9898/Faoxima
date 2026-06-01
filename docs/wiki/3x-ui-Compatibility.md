# 🧩 سازگاری با پنل 3x-ui (صنایی)

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

Faoxima با آخرین نسخهٔ **[3x-ui صنایی (MHSanaei)](https://github.com/MHSanaei/3x-ui)** سازگار است (آداپتور `x-ui_single.php`، سازگار با احراز هویت مبتنی بر **API token / کوکی نشست**).

## ➕ افزودن پنل 3x-ui
1. پنل مدیر → بخش **پنل‌ها / لوکیشن‌ها** (افزودن سرور).
2. نوع پنل را **x-ui / 3x-ui** انتخاب کنید.
3. اطلاعات را وارد کنید:
   - **آدرس پنل** با مسیر پایه؛ مثال: `https://panel.example.com:2053/<webBasePath>`
   - **یوزرنیم و رمز** ادمین 3x-ui
   - در صورت فعال بودن، **مسیر پایهٔ وب (web base path)** را در URL لحاظ کنید.
4. ذخیره و **تست اتصال**.

## 🔐 نکات احراز هویت
- 3x-ui از لاگین مبتنی بر نشست (کوکی) استفاده می‌کند؛ آداپتور این را مدیریت می‌کند.
- اگر **2FA** یا مسیر پایهٔ سفارشی فعال است، آن‌ها را دقیق وارد کنید.
- برای هاست ایران، اگر پنل خارج از کشور است، **[پراکسی پنل‌ها](Proxy-Setup)** را روشن کنید.

## 🧪 تست
- یک محصول/پلن بسازید که به این پنل وصل باشد.
- از طریق ربات یا **[پنل نماینده](Reseller-System)** یک سرویس آزمایشی بسازید و کانفیگ/سابسکریپشن را بررسی کنید.

## 🛠️ مشکلات رایج
| نشانه | علت محتمل | راه‌حل |
|------|-----------|--------|
| خطای لاگین | یوزر/رمز یا web base path اشتباه | URL کامل با مسیر پایه را وارد کنید |
| ساخت کانفیگ ناموفق | inbound/پروتکل نامناسب | inbound فعال و سازگار را انتخاب کنید |
| timeout از ایران | فیلترینگ | پراکسی پنل‌ها را روشن کنید |

---

<a name="-english"></a>
## 🇬🇧 English — 3x-ui (MHSanaei) compatibility

Faoxima is compatible with the latest **[3x-ui by MHSanaei](https://github.com/MHSanaei/3x-ui)** via the `x-ui_single.php` adapter (API‑token / session‑cookie auth).

### Add a 3x-ui panel
Admin → Panels/Locations → add server → type **x-ui / 3x-ui** → enter the panel URL **including the web base path** (e.g. `https://panel.example.com:2053/<webBasePath>`), admin username/password → Save & test.

### Auth notes
3x-ui uses session‑cookie login (handled by the adapter). Include any custom **web base path** and handle 2FA if enabled. For Iran hosts with a foreign panel, enable the **[panels proxy](Proxy-Setup)**.

### Test
Create a product bound to this panel, then provision a test service via the bot or **[reseller panel](Reseller-System)** and verify the config/subscription.

### Common issues
- Login error → wrong credentials or missing web base path → use the full URL with base path.
- Config creation fails → pick an active/compatible inbound.
- Timeout from Iran → enable the panels proxy.
