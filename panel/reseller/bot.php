<?php

/**
 * Multi-bot webhook endpoint (Phase 4).
 *
 * Every reseller bot registers its webhook to:
 *     https://<host>/panel/reseller/bot.php?secret=<bot_secret>
 * Telegram also echoes that secret back in the X-Telegram-Bot-API-Secret-Token
 * header (set during setWebhook), which we verify with hash_equals.
 *
 * The secret resolves to exactly one reseller (bot_secret is unique-ish and we
 * additionally require bot_status = '1'), loading that reseller's context so a
 * single host can serve N reseller bots, dispatched purely by token.
 */

// Never run the legacy main-bot router that lives at the end of botapi.php.
if (!defined('FAOXIMA_SKIP_BOTAPI_ROUTER')) {
    define('FAOXIMA_SKIP_BOTAPI_ROUTER', true);
}

require_once __DIR__ . '/bot_lib.php';

// Always answer 200 quickly; Telegram retries on non-200.
function reseller_bot_ok_exit()
{
    if (!headers_sent()) {
        http_response_code(200);
    }
    exit;
}

$pdo = $GLOBALS['pdo'] ?? null;
if (!($pdo instanceof PDO)) {
    reseller_bot_ok_exit();
}

// Resolve the incoming secret (query param or Telegram header).
$secret = (string) ($_GET['secret'] ?? '');
$headerSecret = isset($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'])
    ? (string) $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN']
    : '';
if ($secret === '' && $headerSecret !== '') {
    $secret = $headerSecret;
}
$secret = preg_replace('/[^A-Za-z0-9_\-]/', '', $secret);
if ($secret === '') {
    reseller_bot_ok_exit();
}

// Match the secret to an active reseller bot.
try {
    $stmt = $pdo->prepare("SELECT * FROM reseller WHERE bot_secret = :s AND bot_status = '1' LIMIT 1");
    $stmt->execute([':s' => $secret]);
    $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    error_log('[reseller bot.php] lookup: ' . $e->getMessage());
    reseller_bot_ok_exit();
}
if (!$reseller) {
    error_log('[reseller bot.php] no active reseller for secret');
    reseller_bot_ok_exit();
}

// Defence in depth: if Telegram sent a header secret, it must match the bot's.
if ($headerSecret !== '' && !hash_equals((string) $reseller['bot_secret'], $headerSecret)) {
    reseller_bot_ok_exit();
}

// The reseller's bot must have a token to talk back to Telegram.
if (trim((string) ($reseller['bot_token'] ?? '')) === '') {
    reseller_bot_ok_exit();
}

// Read and validate the Telegram update.
$raw = @file_get_contents('php://input');
if (!is_string($raw) || $raw === '') {
    reseller_bot_ok_exit();
}
$update = json_decode($raw, true);
if (!is_array($update) || !isset($update['update_id'])) {
    reseller_bot_ok_exit();
}

// Heavy stack (telegram()/sendmessage()/ManagePanel) is required only now.
require_once __DIR__ . '/../../function.php';
require_once __DIR__ . '/../../botapi.php';
require_once __DIR__ . '/../../panels.php';

try {
    reseller_bot_handle($reseller, $update, null);
} catch (\Throwable $e) {
    error_log('[reseller bot.php] handle: ' . $e->getMessage());
}

reseller_bot_ok_exit();
