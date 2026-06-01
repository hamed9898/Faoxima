# 🤖 ربات اختصاصی هر نماینده

> **🇮🇷 فارسی** | [🇬🇧 English](#-english)

هر نماینده می‌تواند **یک ربات تلگرام اختصاصی** به پنل خود متصل کند تا به‌عنوان فروشندهٔ VPN برای مشتریانش عمل کند. معماری **تک‌هاست + چند توکن** است: همهٔ ربات‌های نمایندگان روی همین یک سورس کار می‌کنند و با **secret** در آدرس وب‌هوک از هم تفکیک می‌شوند.

## 🔗 اتصال ربات (از پنل نماینده)
پنل نماینده → **تنظیمات ربات** (`panel/reseller/bot_settings.php`):
1. **توکن ربات** را از [@BotFather](https://t.me/BotFather) بگیرید و وارد کنید.
2. ذخیره کنید — یک **secret وب‌هوک** به‌صورت خودکار ساخته می‌شود.
3. دکمهٔ **ثبت وب‌هوک** را بزنید؛ آدرس وب‌هوک به این شکل است:
   ```
   https://<domain>/panel/reseller/bot.php?secret=<bot_secret>
   ```
4. (اختیاری) **لینک پشتیبانی** و **یوزرنیم ربات** را تنظیم کنید.
5. ربات را روشن/خاموش کنید.

> 🔒 وب‌هوک با **secret** اعتبارسنجی می‌شود؛ درخواست‌های بدون secret معتبر رد می‌شوند.

## 🛒 اتوماسیون مشتری
1. مشتری ربات نماینده را `/start` می‌زند.
2. **کیف‌پول مشتری**: مشتری از طریق **درگاه‌های پنل نماینده** کیف‌پولش را شارژ می‌کند.
3. **خرید**: مشتری مستقیماً از ربات خرید می‌کند → از کیف‌پول مشتری کسر و از موجودیِ پنل نماینده سرویس ساخته و تحویل می‌شود.
4. سرویس به‌صورت کانفیگ/سابسکریپشن + QR به مشتری داده می‌شود (رجوع: [سابسکریپشن و QR](Subscription-and-QR)).

## 💸 جریان مالی (مهم)
- شارژ کیف‌پول مشتری **اتمیک و دقیقاً‌یک‌بار** است؛ در صورت شکست کال‌بک، ردیف به `pending` برمی‌گردد.
- هنگام خرید، نتیجهٔ `reseller_wallet_apply` بررسی می‌شود و خطاهای کسر برای تطبیق دستی **لاگ** می‌شوند.
- نام مشتری روی کال‌بک پرداخت **پاک نمی‌شود** (باگ قبلی رفع شده).

## 🧩 چند ربات همزمان
هر نماینده توکن خودش را می‌دهد؛ سورس بر اساس `secret`/توکن، درخواست هر ربات را به نمایندهٔ درست مسیردهی می‌کند. نیازی به هاست جدا برای هر ربات نیست.

## 🩺 رفع مشکل
| نشانه | راه‌حل |
|------|--------|
| ربات پاسخ نمی‌دهد | secret و وضعیت وب‌هوک را در `bot_settings.php` بررسی کنید؛ مجدداً ثبت وب‌هوک بزنید |
| خطای اتصال (هاست ایران) | **[پراکسی تلگرام](Proxy-Setup)** را روشن کنید |
| پرداخت تأیید نشد | تنظیمات درگاه نماینده + HTTPS/کال‌بک را بررسی کنید |

---

<a name="-english"></a>
## 🇬🇧 English — Per‑reseller Telegram bot

Each reseller can attach **their own Telegram bot** to act as a VPN seller for their customers. Architecture is **single host + multiple tokens**: all reseller bots run on this one source and are separated by a **secret** in the webhook URL.

### Connect (reseller panel)
Reseller panel → **Bot settings** (`panel/reseller/bot_settings.php`):
1. Enter the **bot token** from [@BotFather](https://t.me/BotFather).
2. Save — a **webhook secret** is auto‑generated.
3. Click **Set webhook**; the URL is:
   ```
   https://<domain>/panel/reseller/bot.php?secret=<bot_secret>
   ```
4. Optionally set a **support link** and **bot username**; toggle the bot on/off.

The webhook is **secret‑validated**; requests without a valid secret are rejected.

### Customer automation
Customer `/start`s the reseller bot → tops up a **customer wallet** via the reseller panel’s gateways → buys directly in the bot → the customer wallet is debited and a service is provisioned from the reseller’s inventory and delivered as config/subscription + QR (see [Subscription & QR](Subscription-and-QR)).

### Money flow (important)
Customer top‑ups are **atomic & exactly‑once** (failed callback reverts to `pending`). On purchase, the `reseller_wallet_apply` result is checked and debit errors are **logged** for manual reconciliation. The customer name is **not** wiped on payment callbacks (previous bug fixed).

### Many bots at once
Each reseller supplies their own token; the source routes each bot’s requests to the correct reseller by `secret`/token — no separate host per bot needed.

### Troubleshooting
- Bot silent → check secret + webhook status in `bot_settings.php`, re‑set the webhook.
- Connection error (Iran host) → enable the **[Telegram proxy](Proxy-Setup)**.
- Payment not confirmed → check the reseller’s gateway config + HTTPS/callback.
