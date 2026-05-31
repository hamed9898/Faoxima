<?php

/**
 * Payment helpers for reseller-bot CUSTOMER wallet top-ups (Phase 4).
 *
 * Mirrors panel/reseller/pay_lib.php (which tops up the reseller's own wallet),
 * but credits a customer's per-reseller wallet (`reseller_customer.balance`) and
 * uses its own pending table (`reseller_customer_payment`) and callback URL so
 * the three money flows (admin user balance / reseller wallet / customer wallet)
 * never collide.
 *
 * Reuses the admin's existing gateway credentials (PaySetting) and the Phase-2
 * outbound proxy via reseller_pay_curl()/reseller_pay_setting() from pay_lib.php.
 */

require_once __DIR__ . '/pay_lib.php';

if (!function_exists('reseller_customer_payment_create')) {
    /**
     * Create a pending customer top-up + gateway redirect URL.
     * @return array ['ok'=>bool,'url'=>string,'msg'=>string]
     */
    function reseller_customer_payment_create($resellerId, $customerChatId, $gateway, $amount)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return ['ok' => false, 'msg' => 'no database'];
        }
        $resellerId = (int) $resellerId;
        $customerChatId = (int) $customerChatId;
        $amount = (int) $amount;
        if ($amount < 1000) {
            return ['ok' => false, 'msg' => 'حداقل مبلغ شارژ ۱۰۰۰ تومان است.'];
        }
        $orderId = 'rc' . $resellerId . '-' . $customerChatId . '-' . time() . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
        $origin = reseller_pay_origin();
        $callback = $origin . '/panel/reseller/bot_pay_callback.php?gw=' . $gateway . '&order=' . urlencode($orderId);

        if ($gateway === 'zarinpal') {
            $merchant = trim((string) reseller_pay_setting('merchant_zarinpal'));
            if ($merchant === '') {
                return ['ok' => false, 'msg' => 'درگاه زرین‌پال پیکربندی نشده است.'];
            }
            $resp = reseller_pay_curl('https://api.zarinpal.com/pg/v4/payment/request.json', [
                'merchant_id'  => $merchant,
                'currency'     => 'IRT',
                'amount'       => $amount,
                'callback_url' => $callback,
                'description'  => 'شارژ کیف پول مشتری ' . $orderId,
            ]);
            $authority = $resp['data']['authority'] ?? null;
            $code = $resp['data']['code'] ?? null;
            if (empty($authority) || (int) $code !== 100) {
                return ['ok' => false, 'msg' => 'خطا در ایجاد تراکنش زرین‌پال.'];
            }
            reseller_customer_payment_insert($resellerId, $customerChatId, 'zarinpal', $orderId, $authority, $amount);
            return ['ok' => true, 'url' => 'https://www.zarinpal.com/pg/StartPay/' . $authority];
        }

        if ($gateway === 'aqayepardakht') {
            $pin = trim((string) reseller_pay_setting('merchant_id_aqayepardakht'));
            if ($pin === '') {
                return ['ok' => false, 'msg' => 'درگاه آقای پرداخت پیکربندی نشده است.'];
            }
            $resp = reseller_pay_curl('https://panel.aqayepardakht.ir/api/v2/create', [
                'pin'        => $pin,
                'amount'     => $amount,
                'callback'   => $callback,
                'invoice_id' => $orderId,
            ]);
            $transid = $resp['transid'] ?? null;
            if (($resp['status'] ?? '') !== 'success' || empty($transid)) {
                return ['ok' => false, 'msg' => 'خطا در ایجاد تراکنش آقای پرداخت.'];
            }
            reseller_customer_payment_insert($resellerId, $customerChatId, 'aqayepardakht', $orderId, (string) $transid, $amount);
            $startPath = ($pin === 'sandbox') ? 'startpay/sandbox/' : 'startpay/';
            return ['ok' => true, 'url' => 'https://panel.aqayepardakht.ir/' . $startPath . $transid];
        }

        return ['ok' => false, 'msg' => 'درگاه نامعتبر است.'];
    }
}

if (!function_exists('reseller_customer_payment_insert')) {
    function reseller_customer_payment_insert($resellerId, $customerChatId, $gateway, $orderId, $authority, $amount)
    {
        $pdo = $GLOBALS['pdo'];
        $stmt = $pdo->prepare(
            "INSERT INTO reseller_customer_payment (reseller_id, customer_chat_id, gateway, order_id, authority, amount, status, created_at)
             VALUES (:rid, :cid, :gw, :oid, :auth, :amt, 'pending', :ts)"
        );
        $stmt->execute([
            ':rid'  => (int) $resellerId,
            ':cid'  => (int) $customerChatId,
            ':gw'   => $gateway,
            ':oid'  => $orderId,
            ':auth' => (string) $authority,
            ':amt'  => (int) $amount,
            ':ts'   => (string) time(),
        ]);
    }
}

if (!function_exists('reseller_customer_payment_find')) {
    function reseller_customer_payment_find($orderId)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return null;
        }
        $stmt = $pdo->prepare("SELECT * FROM reseller_customer_payment WHERE order_id = :oid LIMIT 1");
        $stmt->execute([':oid' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('reseller_customer_payment_mark_paid')) {
    /**
     * Atomically flip a pending customer top-up to paid and credit the
     * customer's per-reseller wallet exactly once.
     * @return array ['ok'=>bool,'amount'=>int,'already'=>bool,'msg'=>string,'reseller_id'=>int,'customer_chat_id'=>int]
     */
    function reseller_customer_payment_mark_paid($orderId, $ref = '')
    {
        $pdo = $GLOBALS['pdo'];
        $upd = $pdo->prepare(
            "UPDATE reseller_customer_payment SET status = 'paid', ref = :ref, paid_at = :ts
             WHERE order_id = :oid AND status <> 'paid'"
        );
        $upd->execute([':ref' => mb_substr((string) $ref, 0, 190), ':ts' => (string) time(), ':oid' => $orderId]);
        if ($upd->rowCount() < 1) {
            $row = reseller_customer_payment_find($orderId);
            return [
                'ok' => true, 'amount' => 0, 'already' => true, 'msg' => 'قبلاً پردازش شده',
                'reseller_id' => (int) ($row['reseller_id'] ?? 0),
                'customer_chat_id' => (int) ($row['customer_chat_id'] ?? 0),
            ];
        }
        // اگر بعد از علامت‌گذاری به‌عنوان «paid» مرحله‌ی واریز شکست بخورد، ردیف را به «pending»
        // برمی‌گردانیم تا فراخوانی بعدیِ کال‌بک بتواند دوباره (idempotent) واریز را انجام دهد؛
        // در غیر این صورت تراکنش در شاخه‌ی «already» گیر می‌کرد و کیف پول هرگز شارژ نمی‌شد.
        $revert = static function () use ($pdo, $orderId) {
            $rb = $pdo->prepare("UPDATE reseller_customer_payment SET status = 'pending', paid_at = '' WHERE order_id = :oid AND status = 'paid'");
            $rb->execute([':oid' => $orderId]);
        };
        $row = reseller_customer_payment_find($orderId);
        if (!$row) {
            return ['ok' => false, 'amount' => 0, 'already' => false, 'msg' => 'تراکنش یافت نشد', 'reseller_id' => 0, 'customer_chat_id' => 0];
        }
        $customer = reseller_customer_get((int) $row['reseller_id'], (int) $row['customer_chat_id']);
        if (!$customer) {
            $revert();
            return ['ok' => false, 'amount' => 0, 'already' => false, 'msg' => 'مشتری یافت نشد', 'reseller_id' => (int) $row['reseller_id'], 'customer_chat_id' => (int) $row['customer_chat_id']];
        }
        $apply = reseller_customer_wallet_apply((int) $customer['id'], (int) $row['amount']);
        if (empty($apply['ok'])) {
            $revert();
            error_log('[reseller_customer_payment_mark_paid] wallet credit failed for order ' . $orderId . '; reverted to pending: ' . (string) ($apply['msg'] ?? 'unknown'));
        }
        return [
            'ok' => $apply['ok'],
            'amount' => (int) $row['amount'],
            'already' => false,
            'msg' => $apply['msg'],
            'reseller_id' => (int) $row['reseller_id'],
            'customer_chat_id' => (int) $row['customer_chat_id'],
        ];
    }
}

if (!function_exists('reseller_customer_payment_verify')) {
    /** Confirm a pending customer payment with the gateway, then credit the wallet. */
    function reseller_customer_payment_verify(array $payment)
    {
        $gateway = (string) $payment['gateway'];
        $amount = (int) $payment['amount'];

        if ($gateway === 'zarinpal') {
            $merchant = trim((string) reseller_pay_setting('merchant_zarinpal'));
            $resp = reseller_pay_curl('https://api.zarinpal.com/pg/v4/payment/verify.json', [
                'merchant_id' => $merchant,
                'amount'      => $amount,
                'authority'   => (string) $payment['authority'],
            ]);
            $msg = $resp['data']['message'] ?? '';
            $code = (int) ($resp['data']['code'] ?? 0);
            $refId = $resp['data']['ref_id'] ?? '';
            if ($code === 100 || $code === 101 || $msg === 'Verified' || $msg === 'Paid') {
                return reseller_customer_payment_mark_paid((string) $payment['order_id'], (string) $refId);
            }
            return ['ok' => false, 'amount' => 0, 'already' => false, 'msg' => 'تأیید پرداخت ناموفق بود', 'reseller_id' => (int) $payment['reseller_id'], 'customer_chat_id' => (int) $payment['customer_chat_id']];
        }

        if ($gateway === 'aqayepardakht') {
            $pin = trim((string) reseller_pay_setting('merchant_id_aqayepardakht'));
            $resp = reseller_pay_curl('https://panel.aqayepardakht.ir/api/v2/verify', [
                'pin'     => $pin,
                'amount'  => $amount,
                'transid' => (string) $payment['authority'],
            ]);
            $code = (string) ($resp['code'] ?? '');
            if ($code === '1' || $code === '2') {
                return reseller_customer_payment_mark_paid((string) $payment['order_id'], (string) $payment['authority']);
            }
            return ['ok' => false, 'amount' => 0, 'already' => false, 'msg' => 'تأیید پرداخت ناموفق بود', 'reseller_id' => (int) $payment['reseller_id'], 'customer_chat_id' => (int) $payment['customer_chat_id']];
        }

        return ['ok' => false, 'amount' => 0, 'already' => false, 'msg' => 'درگاه نامعتبر', 'reseller_id' => (int) $payment['reseller_id'], 'customer_chat_id' => (int) $payment['customer_chat_id']];
    }
}
