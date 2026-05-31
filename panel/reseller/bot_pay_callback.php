<?php

/**
 * Gateway return endpoint for reseller-bot CUSTOMER wallet top-ups (Phase 4).
 *
 * Zarinpal redirects here via GET (?Authority=&Status=); AqayePardakht posts
 * back invoice_id + transid. We confirm the pending top-up with the gateway,
 * credit the customer's per-reseller wallet exactly once, then push a Telegram
 * confirmation to the customer through the reseller's own bot token.
 */

require_once __DIR__ . '/bot_pay_lib.php';

$gw = isset($_GET['gw']) ? preg_replace('/[^a-z]/', '', (string) $_GET['gw']) : '';
$orderId = (string) ($_GET['order'] ?? $_POST['invoice_id'] ?? '');
$orderId = preg_replace('/[^A-Za-z0-9_\-]/', '', $orderId);

$result = ['ok' => false, 'amount' => 0, 'msg' => 'تراکنش یافت نشد', 'reseller_id' => 0, 'customer_chat_id' => 0];
$payment = $orderId !== '' ? reseller_customer_payment_find($orderId) : null;

if ($payment) {
    $status = (string) ($_GET['Status'] ?? 'OK');
    if ($gw === 'zarinpal' && $status !== 'OK') {
        $result = ['ok' => false, 'amount' => 0, 'msg' => 'پرداخت توسط کاربر لغو شد', 'reseller_id' => (int) $payment['reseller_id'], 'customer_chat_id' => (int) $payment['customer_chat_id']];
    } elseif ((string) $payment['status'] === 'paid') {
        $result = ['ok' => true, 'amount' => (int) $payment['amount'], 'msg' => 'این تراکنش قبلاً تأیید شده است', 'reseller_id' => (int) $payment['reseller_id'], 'customer_chat_id' => (int) $payment['customer_chat_id']];
    } else {
        $result = reseller_customer_payment_verify($payment);
    }
}

$ok = !empty($result['ok']);
$amount = (int) ($result['amount'] ?? 0);
$msg = (string) ($result['msg'] ?? '');

// Notify the customer through the reseller's bot (best-effort).
if ($ok && $amount > 0 && (int) ($result['customer_chat_id'] ?? 0) > 0) {
    try {
        $reseller = reseller_find_by_id((int) $result['reseller_id']);
        $botToken = $reseller ? trim((string) ($reseller['bot_token'] ?? '')) : '';
        if ($botToken !== '') {
            require_once __DIR__ . '/../../function.php';
            require_once __DIR__ . '/../../botapi.php';
            $customer = reseller_customer_get((int) $result['reseller_id'], (int) $result['customer_chat_id']);
            $balanceLine = $customer ? "\n💰 موجودی فعلی: " . number_format((int) $customer['balance']) . ' تومان' : '';
            sendmessage(
                (int) $result['customer_chat_id'],
                "✅ پرداخت موفق\nمبلغ " . number_format($amount) . " تومان به کیف پول شما اضافه شد." . $balanceLine,
                json_encode(['inline_keyboard' => [[['text' => '🛒 خرید سرویس', 'callback_data' => 'buy']], [['text' => '🏠 منوی اصلی', 'callback_data' => 'home']]]]),
                '',
                $botToken
            );
        }
    } catch (\Throwable $e) {
        error_log('[bot_pay_callback] notify: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="dark" data-color="blue">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نتیجه پرداخت</title>
    <link rel="stylesheet" href="../css/theme.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-body" style="text-align:center;">
            <div style="font-size:54px; color:<?php echo $ok ? 'var(--success,#22c55e)' : 'var(--danger,#ef4444)'; ?>;">
                <?php echo icon($ok ? 'circle-check' : 'circle-exclamation', 'svg-icon'); ?>
            </div>
            <h2><?php echo $ok ? 'پرداخت موفق' : 'پرداخت ناموفق'; ?></h2>
            <?php if ($ok && $amount > 0): ?>
                <p>مبلغ <b><?php echo number_format($amount); ?></b> تومان به کیف پول شما اضافه شد.</p>
            <?php endif; ?>
            <?php if ($msg !== ''): ?>
                <p class="text-muted"><?php echo reseller_e($msg); ?></p>
            <?php endif; ?>
            <p class="text-muted">می‌توانید به ربات تلگرام بازگردید.</p>
        </div>
    </div>
</body>
</html>
