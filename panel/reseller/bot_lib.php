<?php

/**
 * Reseller sales-bot handler (Phase 4).
 *
 * One Telegram bot per reseller, all served from a single host. Each reseller's
 * bot posts updates to bot.php?secret=<bot_secret>; that endpoint resolves the
 * reseller, then hands the update to reseller_bot_handle() here.
 *
 * Customers can:
 *   - top up a per-reseller wallet via the admin's existing payment gateways,
 *   - buy any product the admin flagged sellable (at the reseller's sell price),
 *   - view their active services (subscription link + QR page).
 *
 * Money model (three isolated wallets never collide):
 *   - admin user balance  -> unchanged
 *   - reseller wallet      -> reseller.balance (+sale revenue, -provision cost)
 *   - customer wallet      -> reseller_customer.balance (-purchase, +top-up)
 */

require_once __DIR__ . '/bot_pay_lib.php';

if (!function_exists('reseller_bot_kb')) {
    /** Build a JSON inline keyboard from a rows-of-[text,data] structure. */
    function reseller_bot_kb(array $rows)
    {
        $kb = [];
        foreach ($rows as $row) {
            $line = [];
            foreach ($row as $btn) {
                if (isset($btn['url'])) {
                    $line[] = ['text' => $btn['text'], 'url' => $btn['url']];
                } else {
                    $line[] = ['text' => $btn['text'], 'callback_data' => $btn['data']];
                }
            }
            if ($line) {
                $kb[] = $line;
            }
        }
        return json_encode(['inline_keyboard' => $kb], JSON_UNESCAPED_UNICODE);
    }
}

if (!function_exists('reseller_bot_main_menu_kb')) {
    function reseller_bot_main_menu_kb(array $reseller)
    {
        $rows = [
            [['text' => '🛒 خرید سرویس', 'data' => 'buy']],
            [['text' => '💰 شارژ کیف پول', 'data' => 'topup'], ['text' => '👛 کیف پول من', 'data' => 'wallet']],
            [['text' => '📦 سرویس‌های من', 'data' => 'services']],
        ];
        $support = trim((string) ($reseller['support_link'] ?? ''));
        if ($support !== '') {
            $rows[] = [['text' => '🆘 پشتیبانی', 'url' => $support]];
        }
        return reseller_bot_kb($rows);
    }
}

if (!function_exists('reseller_bot_origin')) {
    function reseller_bot_origin()
    {
        return reseller_pay_origin();
    }
}

if (!function_exists('reseller_bot_send')) {
    function reseller_bot_send(array $reseller, $chatId, $text, $keyboard = '')
    {
        $token = trim((string) ($reseller['bot_token'] ?? ''));
        if ($token === '') {
            return;
        }
        if ($keyboard === '') {
            $keyboard = json_encode(['inline_keyboard' => []]);
        }
        sendmessage((int) $chatId, $text, $keyboard, 'HTML', $token);
    }
}

if (!function_exists('reseller_bot_answer_cb')) {
    function reseller_bot_answer_cb(array $reseller, $callbackId, $text = '')
    {
        $token = trim((string) ($reseller['bot_token'] ?? ''));
        if ($token === '' || $callbackId === '') {
            return;
        }
        telegram('answerCallbackQuery', ['callback_query_id' => $callbackId, 'text' => $text], $token);
    }
}

if (!function_exists('reseller_bot_welcome')) {
    function reseller_bot_welcome(array $reseller, array $customer)
    {
        $name = trim((string) ($customer['first_name'] ?? '')) ?: 'کاربر';
        $shop = trim((string) ($reseller['name'] ?? $reseller['username'] ?? 'فروشگاه'));
        $bal = number_format((int) ($customer['balance'] ?? 0));
        $text = "👋 سلام <b>" . reseller_e($name) . "</b>\n"
            . "به ربات فروش <b>" . reseller_e($shop) . "</b> خوش آمدید.\n\n"
            . "💳 موجودی کیف پول شما: <b>{$bal}</b> تومان\n\n"
            . "از منوی زیر استفاده کنید:";
        reseller_bot_send($reseller, (int) $customer['chat_id'], $text, reseller_bot_main_menu_kb($reseller));
    }
}

if (!function_exists('reseller_bot_show_wallet')) {
    function reseller_bot_show_wallet(array $reseller, array $customer)
    {
        $bal = number_format((int) ($customer['balance'] ?? 0));
        $text = "👛 <b>کیف پول شما</b>\n\n💳 موجودی: <b>{$bal}</b> تومان";
        $kb = reseller_bot_kb([
            [['text' => '💰 شارژ کیف پول', 'data' => 'topup']],
            [['text' => '🏠 منوی اصلی', 'data' => 'home']],
        ]);
        reseller_bot_send($reseller, (int) $customer['chat_id'], $text, $kb);
    }
}

if (!function_exists('reseller_bot_show_products')) {
    function reseller_bot_show_products(array $reseller, array $customer)
    {
        $products = reseller_allowed_products($reseller);
        if (!$products) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "در حال حاضر محصولی برای فروش موجود نیست.", reseller_bot_kb([[['text' => '🏠 منوی اصلی', 'data' => 'home']]]));
            return;
        }
        $rows = [];
        foreach ($products as $p) {
            $price = reseller_sell_price((int) $reseller['id'], $p);
            $title = trim((string) ($p['name_product'] ?? $p['code_product']));
            $rows[] = [['text' => $title . ' — ' . number_format($price) . ' ت', 'data' => 'p:' . $p['code_product']]];
        }
        $rows[] = [['text' => '🏠 منوی اصلی', 'data' => 'home']];
        reseller_bot_send($reseller, (int) $customer['chat_id'], "🛒 <b>محصولات موجود</b>\nیک محصول را انتخاب کنید:", reseller_bot_kb($rows));
    }
}

if (!function_exists('reseller_bot_show_product')) {
    function reseller_bot_show_product(array $reseller, array $customer, $code)
    {
        $product = reseller_find_product($reseller, $code);
        if (!$product) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "محصول یافت نشد.", reseller_bot_kb([[['text' => '🛒 بازگشت', 'data' => 'buy']]]));
            return;
        }
        $price = reseller_sell_price((int) $reseller['id'], $product);
        $vol = (float) ($product['Volume_constraint'] ?? 0);
        $days = (int) ($product['Service_time'] ?? 0);
        $volTxt = $vol > 0 ? (rtrim(rtrim(number_format($vol, 2), '0'), '.') . ' گیگابایت') : 'نامحدود';
        $daysTxt = $days > 0 ? ($days . ' روز') : 'نامحدود';
        $bal = (int) ($customer['balance'] ?? 0);
        $text = "📦 <b>" . reseller_e((string) ($product['name_product'] ?? $product['code_product'])) . "</b>\n\n"
            . "📊 حجم: <b>{$volTxt}</b>\n"
            . "⏳ مدت: <b>{$daysTxt}</b>\n"
            . "💵 قیمت: <b>" . number_format($price) . "</b> تومان\n"
            . "💳 موجودی شما: <b>" . number_format($bal) . "</b> تومان";
        $rows = [];
        if ($bal >= $price) {
            $rows[] = [['text' => '✅ خرید و دریافت سرویس', 'data' => 'cf:' . $code]];
        } else {
            $text .= "\n\n⚠️ موجودی کافی نیست. ابتدا کیف پول را شارژ کنید.";
            $rows[] = [['text' => '💰 شارژ کیف پول', 'data' => 'topup']];
        }
        $rows[] = [['text' => '🛒 بازگشت به محصولات', 'data' => 'buy']];
        reseller_bot_send($reseller, (int) $customer['chat_id'], $text, reseller_bot_kb($rows));
    }
}

if (!function_exists('reseller_bot_gen_username')) {
    function reseller_bot_gen_username(array $reseller, array $customer)
    {
        $prefix = preg_replace('/[^A-Za-z0-9]/', '', (string) ($reseller['username'] ?? 'rs'));
        $prefix = $prefix !== '' ? substr($prefix, 0, 6) : 'rs';
        return strtolower($prefix) . (int) $customer['chat_id'] . substr(bin2hex(random_bytes(2)), 0, 3);
    }
}

if (!function_exists('reseller_bot_purchase')) {
    /**
     * Execute a customer purchase: debit customer wallet, provision on panel,
     * refund on failure, then record reseller sale revenue and provision cost.
     */
    function reseller_bot_purchase(array $reseller, array $customer, $code, $ManagePanel)
    {
        $chatId = (int) $customer['chat_id'];
        $product = reseller_find_product($reseller, $code);
        if (!$product) {
            reseller_bot_send($reseller, $chatId, "محصول یافت نشد.", reseller_bot_main_menu_kb($reseller));
            return;
        }
        $rid = (int) $reseller['id'];
        $sell = reseller_sell_price($rid, $product);
        $cost = reseller_product_price($product);

        // Resolve destination panel (product Location or first available).
        $panelName = reseller_resolve_panel($product, '');
        if ($panelName === '') {
            reseller_bot_send($reseller, $chatId, "⚠️ پنل مقصد در دسترس نیست. لطفاً با پشتیبانی تماس بگیرید.", reseller_bot_main_menu_kb($reseller));
            return;
        }

        // Reseller must be able to afford the provisioning cost from admin.
        $freshReseller = reseller_find_by_id($rid);
        if (!$freshReseller || (int) $freshReseller['balance'] < $cost) {
            reseller_bot_send($reseller, $chatId, "⚠️ این سرویس موقتاً در دسترس نیست. لطفاً بعداً تلاش کنید.", reseller_bot_main_menu_kb($reseller));
            error_log('[reseller_bot_purchase] reseller ' . $rid . ' has insufficient balance for provisioning cost ' . $cost);
            return;
        }

        // 1) Take the customer's money first (atomic, exactly once).
        $debit = reseller_customer_wallet_apply((int) $customer['id'], -$sell);
        if (!$debit['ok']) {
            reseller_bot_send($reseller, $chatId, "⚠️ " . ($debit['msg'] ?: 'موجودی کافی نیست') . ".", reseller_bot_main_menu_kb($reseller));
            return;
        }

        // 2) Provision on the panel.
        $username = reseller_bot_gen_username($reseller, $customer);
        $prov = reseller_provision_service($reseller, $product, $panelName, $username, $ManagePanel, [
            'customer_chat' => (string) $chatId,
            'customer_name' => (string) ($customer['first_name'] ?? ''),
            'price'         => $cost,
            'sell_price'    => $sell,
            'sold_via'      => 'bot',
        ]);

        // 3) Refund the customer if provisioning failed.
        if (!$prov['ok']) {
            reseller_customer_wallet_apply((int) $customer['id'], $sell);
            reseller_bot_send($reseller, $chatId, "❌ ساخت سرویس ناموفق بود و مبلغ به کیف پول شما بازگشت. \n" . reseller_e((string) $prov['msg']), reseller_bot_main_menu_kb($reseller));
            return;
        }

        // 4) Record reseller accounting: sale revenue then provisioning cost.
        reseller_wallet_apply($rid, 'sale', $sell, 'فروش از ربات: ' . ($product['name_product'] ?? $code), 'cust:' . $chatId);
        reseller_wallet_apply($rid, 'purchase', -$cost, 'هزینه ساخت سرویس ربات: ' . ($product['name_product'] ?? $code), $username);

        // 5) Deliver the subscription.
        $subPage = reseller_bot_origin() . '/panel/reseller/subscription.php?token=' . $prov['sub_token'];
        $subLink = trim((string) ($prov['sub_link'] ?? ''));
        $text = "✅ <b>سرویس شما با موفقیت ساخته شد!</b>\n\n"
            . "📦 محصول: <b>" . reseller_e((string) ($product['name_product'] ?? $code)) . "</b>\n"
            . "👤 نام کاربری: <code>" . reseller_e($username) . "</code>\n\n";
        if ($subLink !== '') {
            $text .= "🔗 لینک اشتراک:\n<code>" . reseller_e($subLink) . "</code>\n\n";
        }
        $text .= "📱 صفحه اشتراک و QR کد:";
        $kb = reseller_bot_kb([
            [['text' => '📱 مشاهده QR و کانفیگ‌ها', 'url' => $subPage]],
            [['text' => '📦 سرویس‌های من', 'data' => 'services']],
            [['text' => '🏠 منوی اصلی', 'data' => 'home']],
        ]);
        reseller_bot_send($reseller, $chatId, $text, $kb);
    }
}

if (!function_exists('reseller_bot_show_services')) {
    function reseller_bot_show_services(array $reseller, array $customer)
    {
        $pdo = $GLOBALS['pdo'];
        $stmt = $pdo->prepare("SELECT * FROM reseller_service WHERE reseller_id = :r AND customer_chat_id = :c ORDER BY id DESC LIMIT 20");
        $stmt->execute([':r' => (int) $reseller['id'], ':c' => (string) $customer['chat_id']]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "شما هنوز سرویسی ندارید.", reseller_bot_kb([[['text' => '🛒 خرید سرویس', 'data' => 'buy']], [['text' => '🏠 منوی اصلی', 'data' => 'home']]]));
            return;
        }
        $origin = reseller_bot_origin();
        $kbRows = [];
        $text = "📦 <b>سرویس‌های شما</b>\n\n";
        foreach ($rows as $i => $svc) {
            $n = $i + 1;
            $uname = reseller_e((string) ($svc['username'] ?? ''));
            $status = ((string) ($svc['status'] ?? '') === 'active') ? '🟢 فعال' : '🔴 غیرفعال';
            $exp = trim((string) ($svc['expire_at'] ?? ''));
            $expTxt = ($exp !== '' && is_numeric($exp)) ? date('Y/m/d', (int) $exp) : 'نامحدود';
            $text .= "{$n}. <code>{$uname}</code> — {$status} — انقضا: {$expTxt}\n";
            $kbRows[] = [['text' => "📱 سرویس {$n}", 'url' => $origin . '/panel/reseller/subscription.php?token=' . $svc['sub_token']]];
        }
        $kbRows[] = [['text' => '🏠 منوی اصلی', 'data' => 'home']];
        reseller_bot_send($reseller, (int) $customer['chat_id'], $text, reseller_bot_kb($kbRows));
    }
}

if (!function_exists('reseller_bot_prompt_topup')) {
    function reseller_bot_prompt_topup(array $reseller, array $customer)
    {
        $gateways = reseller_pay_gateways();
        if (!$gateways) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "⚠️ در حال حاضر درگاه پرداختی پیکربندی نشده است. با پشتیبانی تماس بگیرید.", reseller_bot_main_menu_kb($reseller));
            return;
        }
        reseller_customer_set_step((int) $customer['id'], 'await_topup_amount');
        $rows = [];
        foreach ([50000, 100000, 200000, 500000] as $amt) {
            $rows[] = [['text' => number_format($amt) . ' تومان', 'data' => 'amt:' . $amt]];
        }
        $rows[] = [['text' => '🏠 منوی اصلی', 'data' => 'home']];
        reseller_bot_send($reseller, (int) $customer['chat_id'], "💰 مبلغ شارژ را انتخاب کنید یا عدد دلخواه (تومان) را ارسال کنید:", reseller_bot_kb($rows));
    }
}

if (!function_exists('reseller_bot_choose_gateway')) {
    function reseller_bot_choose_gateway(array $reseller, array $customer, $amount)
    {
        $amount = (int) $amount;
        if ($amount < 1000) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "حداقل مبلغ شارژ ۱۰۰۰ تومان است.", reseller_bot_main_menu_kb($reseller));
            return;
        }
        $gateways = reseller_pay_gateways();
        if (!$gateways) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "⚠️ درگاه پرداختی موجود نیست.", reseller_bot_main_menu_kb($reseller));
            return;
        }
        reseller_customer_set_step((int) $customer['id'], '');
        $rows = [];
        foreach ($gateways as $gw => $label) {
            $rows[] = [['text' => $label, 'data' => 'gw:' . $gw . ':' . $amount]];
        }
        $rows[] = [['text' => '🏠 منوی اصلی', 'data' => 'home']];
        reseller_bot_send($reseller, (int) $customer['chat_id'], "مبلغ <b>" . number_format($amount) . "</b> تومان\nدرگاه پرداخت را انتخاب کنید:", reseller_bot_kb($rows));
    }
}

if (!function_exists('reseller_bot_start_payment')) {
    function reseller_bot_start_payment(array $reseller, array $customer, $gateway, $amount)
    {
        $res = reseller_customer_payment_create((int) $reseller['id'], (int) $customer['chat_id'], $gateway, (int) $amount);
        if (!$res['ok']) {
            reseller_bot_send($reseller, (int) $customer['chat_id'], "❌ " . reseller_e((string) $res['msg']), reseller_bot_main_menu_kb($reseller));
            return;
        }
        $kb = reseller_bot_kb([
            [['text' => '💳 پرداخت', 'url' => $res['url']]],
            [['text' => '🏠 منوی اصلی', 'data' => 'home']],
        ]);
        reseller_bot_send($reseller, (int) $customer['chat_id'], "برای پرداخت روی دکمه زیر بزنید. پس از پرداخت موفق، کیف پول شما به‌صورت خودکار شارژ می‌شود.", $kb);
    }
}

if (!function_exists('reseller_bot_handle')) {
    /**
     * Entry point: handle one Telegram update for a given reseller bot.
     * $ManagePanel may be null until a purchase actually needs it.
     */
    function reseller_bot_handle(array $reseller, array $update, $ManagePanel = null)
    {
        $chatId = (int) ($update['message']['chat']['id']
            ?? $update['callback_query']['message']['chat']['id']
            ?? $update['message']['from']['id']
            ?? $update['callback_query']['from']['id']
            ?? 0);
        if ($chatId === 0) {
            return;
        }
        $firstName = (string) ($update['message']['from']['first_name'] ?? $update['callback_query']['from']['first_name'] ?? '');
        $username = (string) ($update['message']['from']['username'] ?? $update['callback_query']['from']['username'] ?? '');
        $text = trim((string) ($update['message']['text'] ?? ''));
        $data = (string) ($update['callback_query']['data'] ?? '');
        $callbackId = (string) ($update['callback_query']['id'] ?? '');

        $customer = reseller_customer_get((int) $reseller['id'], $chatId, $firstName, $username);
        if (!$customer) {
            return;
        }

        // ----- Text messages -----
        if ($data === '' && $text !== '') {
            if ($text === '/start' || $text === 'start' || mb_strpos($text, '/start') === 0) {
                reseller_customer_set_step((int) $customer['id'], '');
                reseller_bot_welcome($reseller, $customer);
                return;
            }
            // Pending top-up amount entry.
            if ((string) ($customer['step'] ?? '') === 'await_topup_amount') {
                $amt = (int) preg_replace('/[^0-9]/', '', convertPersianNumbersToEnglish_safe($text));
                if ($amt >= 1000) {
                    reseller_bot_choose_gateway($reseller, $customer, $amt);
                } else {
                    reseller_bot_send($reseller, $chatId, "لطفاً یک مبلغ معتبر (حداقل ۱۰۰۰ تومان) ارسال کنید.");
                }
                return;
            }
            // Default: show the menu.
            reseller_bot_welcome($reseller, $customer);
            return;
        }

        // ----- Callback queries -----
        if ($data !== '') {
            reseller_bot_answer_cb($reseller, $callbackId);
            if ($data === 'home') {
                reseller_customer_set_step((int) $customer['id'], '');
                reseller_bot_welcome($reseller, $customer);
            } elseif ($data === 'wallet') {
                reseller_bot_show_wallet($reseller, $customer);
            } elseif ($data === 'buy') {
                reseller_bot_show_products($reseller, $customer);
            } elseif ($data === 'services') {
                reseller_bot_show_services($reseller, $customer);
            } elseif ($data === 'topup') {
                reseller_bot_prompt_topup($reseller, $customer);
            } elseif (strpos($data, 'p:') === 0) {
                reseller_bot_show_product($reseller, $customer, substr($data, 2));
            } elseif (strpos($data, 'cf:') === 0) {
                if (!($ManagePanel instanceof ManagePanel)) {
                    $ManagePanel = new ManagePanel();
                }
                reseller_bot_purchase($reseller, $customer, substr($data, 3), $ManagePanel);
            } elseif (strpos($data, 'amt:') === 0) {
                reseller_bot_choose_gateway($reseller, $customer, (int) substr($data, 4));
            } elseif (strpos($data, 'gw:') === 0) {
                $parts = explode(':', $data);
                $gw = $parts[1] ?? '';
                $amt = (int) ($parts[2] ?? 0);
                reseller_bot_start_payment($reseller, $customer, $gw, $amt);
            }
            return;
        }
    }
}

if (!function_exists('convertPersianNumbersToEnglish_safe')) {
    function convertPersianNumbersToEnglish_safe($s)
    {
        if (function_exists('convertPersianNumbersToEnglish')) {
            return convertPersianNumbersToEnglish($s);
        }
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $ar = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($ar, $en, str_replace($fa, $en, (string) $s));
    }
}
