<?php

/**
 * Reseller "Sales Bot" settings (Phase 4).
 *
 * Lets a reseller connect their own Telegram bot: store the bot token, generate
 * a webhook secret, register/remove the webhook with Telegram, set a support
 * link, and toggle the bot on/off. The webhook points at bot.php?secret=… so a
 * single host can serve every reseller's bot, dispatched purely by token.
 */

require_once __DIR__ . '/layout.php';

$reseller = reseller_require_login();
$pdo = $GLOBALS['pdo'];
$rid = (int) $reseller['id'];

// telegram() lives in botapi.php; the router there is skipped via the constant
// defined in lib.php, so requiring it here only loads the helper functions.
require_once __DIR__ . '/../../function.php';
require_once __DIR__ . '/../../botapi.php';

function reseller_bot_webhook_url(array $reseller)
{
    $secret = (string) ($reseller['bot_secret'] ?? '');
    return reseller_pay_origin() . '/panel/reseller/bot.php?secret=' . rawurlencode($secret);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    reseller_csrf_check();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'save') {
        $token = trim((string) ($_POST['bot_token'] ?? ''));
        $support = trim((string) ($_POST['support_link'] ?? ''));
        if ($token !== '' && !preg_match('~^\d{6,}:[A-Za-z0-9_\-]{20,}$~', $token)) {
            reseller_flash_set('error', 'فرمت توکن ربات معتبر نیست.');
            header('Location: bot_settings.php');
            exit;
        }
        if ($support !== '' && !preg_match('~^https?://~i', $support)) {
            $support = 'https://t.me/' . ltrim($support, '@');
        }
        // Auto-generate a webhook secret on first save.
        $secret = (string) ($reseller['bot_secret'] ?? '');
        if ($secret === '') {
            $secret = bin2hex(random_bytes(24));
        }
        // Try to fetch the bot username for display.
        $botUsername = (string) ($reseller['bot_username'] ?? '');
        if ($token !== '') {
            $me = telegram('getMe', [], $token);
            if (is_array($me) && !empty($me['ok']) && !empty($me['result']['username'])) {
                $botUsername = (string) $me['result']['username'];
            }
        }
        $upd = $pdo->prepare("UPDATE reseller SET bot_token = :t, bot_secret = :s, bot_username = :u, support_link = :sup WHERE id = :id");
        $upd->execute([':t' => $token, ':s' => $secret, ':u' => $botUsername, ':sup' => mb_substr($support, 0, 190), ':id' => $rid]);
        reseller_flash_set('success', 'تنظیمات ربات ذخیره شد.');
        header('Location: bot_settings.php');
        exit;
    }

    if ($action === 'register') {
        $token = trim((string) ($reseller['bot_token'] ?? ''));
        $secret = (string) ($reseller['bot_secret'] ?? '');
        if ($token === '' || $secret === '') {
            reseller_flash_set('error', 'ابتدا توکن ربات را ذخیره کنید.');
            header('Location: bot_settings.php');
            exit;
        }
        $url = reseller_bot_webhook_url($reseller);
        $res = telegram('setWebhook', [
            'url'             => $url,
            'secret_token'    => $secret,
            'max_connections' => 40,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ], $token);
        if (is_array($res) && !empty($res['ok'])) {
            $pdo->prepare("UPDATE reseller SET bot_status = '1' WHERE id = :id")->execute([':id' => $rid]);
            reseller_flash_set('success', 'وب‌هوک با موفقیت ثبت شد و ربات فعال است.');
        } else {
            $desc = is_array($res) ? (string) ($res['description'] ?? 'نامشخص') : 'نامشخص';
            reseller_flash_set('error', 'ثبت وب‌هوک ناموفق بود: ' . $desc);
        }
        header('Location: bot_settings.php');
        exit;
    }

    if ($action === 'disable') {
        $token = trim((string) ($reseller['bot_token'] ?? ''));
        if ($token !== '') {
            telegram('deleteWebhook', [], $token);
        }
        $pdo->prepare("UPDATE reseller SET bot_status = '0' WHERE id = :id")->execute([':id' => $rid]);
        reseller_flash_set('success', 'ربات غیرفعال شد و وب‌هوک حذف گردید.');
        header('Location: bot_settings.php');
        exit;
    }
}

// Reload fresh row (secret may have just been generated).
$reseller = reseller_find_by_id($rid) ?: $reseller;
$hasToken = trim((string) ($reseller['bot_token'] ?? '')) !== '';
$isActive = (string) ($reseller['bot_status'] ?? '0') === '1';
$webhookUrl = reseller_bot_webhook_url($reseller);
$botUsername = trim((string) ($reseller['bot_username'] ?? ''));

reseller_layout_head('ربات فروش', 'bot', $reseller);
?>
<div class="page-head">
    <div>
        <div class="page-head__title"><?php echo icon('robot', 'svg-icon svg-lg'); ?> ربات فروش تلگرام</div>
        <div class="page-head__sub">ربات اختصاصی خود را به پنل متصل کنید تا مشتریان مستقیماً خرید کنند</div>
    </div>
</div>

<?php reseller_flash_render(); ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card__icon <?php echo $isActive ? 'icon-green' : 'icon-red'; ?>"><?php echo icon('robot', 'svg-icon'); ?></div>
        <div class="stat-card__info">
            <span class="stat-card__value"><?php echo $isActive ? 'فعال' : 'غیرفعال'; ?></span>
            <span class="stat-card__label">وضعیت ربات</span>
        </div>
    </div>
    <?php if ($botUsername !== ''): ?>
    <div class="stat-card">
        <div class="stat-card__icon icon-blue"><?php echo icon('user-tag', 'svg-icon'); ?></div>
        <div class="stat-card__info">
            <span class="stat-card__value">@<?php echo reseller_e($botUsername); ?></span>
            <span class="stat-card__label">شناسه ربات</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card mt-3">
    <div class="card__head"><div class="card__title"><?php echo icon('gear', 'svg-icon svg-sm'); ?> تنظیمات ربات</div></div>
    <div style="padding:16px;">
        <div class="alert alert-info">
            <?php echo icon('circle-info', 'svg-icon'); ?>
            <span>توکن ربات را از <b>@BotFather</b> دریافت کنید، اینجا ذخیره کنید، سپس روی «ثبت وب‌هوک و فعال‌سازی» بزنید.</span>
        </div>
        <form method="post" action="bot_settings.php">
            <?php echo reseller_csrf_field(); ?>
            <input type="hidden" name="action" value="save">
            <div class="form-group">
                <label class="form-label">توکن ربات تلگرام</label>
                <input type="text" name="bot_token" class="form-control" dir="ltr"
                       value="<?php echo reseller_e((string) ($reseller['bot_token'] ?? '')); ?>"
                       placeholder="123456789:AA...">
            </div>
            <div class="form-group">
                <label class="form-label">لینک پشتیبانی (اختیاری)</label>
                <input type="text" name="support_link" class="form-control" dir="ltr"
                       value="<?php echo reseller_e((string) ($reseller['support_link'] ?? '')); ?>"
                       placeholder="https://t.me/yoursupport یا yoursupport@">
            </div>
            <button type="submit" class="btn btn-primary mt-2">
                <?php echo icon('save', 'svg-icon svg-sm'); ?> ذخیره تنظیمات
            </button>
        </form>
    </div>
</div>

<?php if ($hasToken): ?>
<div class="card mt-3">
    <div class="card__head"><div class="card__title"><?php echo icon('globe', 'svg-icon svg-sm'); ?> وب‌هوک و فعال‌سازی</div></div>
    <div style="padding:16px;">
        <div class="form-group">
            <label class="form-label">آدرس وب‌هوک</label>
            <input type="text" class="form-control" dir="ltr" readonly value="<?php echo reseller_e($webhookUrl); ?>"
                   onclick="this.select()">
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <form method="post" action="bot_settings.php" style="display:inline;">
                <?php echo reseller_csrf_field(); ?>
                <input type="hidden" name="action" value="register">
                <button type="submit" class="btn btn-success">
                    <?php echo icon('circle-check', 'svg-icon svg-sm'); ?> ثبت وب‌هوک و فعال‌سازی
                </button>
            </form>
            <?php if ($isActive): ?>
            <form method="post" action="bot_settings.php" style="display:inline;">
                <?php echo reseller_csrf_field(); ?>
                <input type="hidden" name="action" value="disable">
                <button type="submit" class="btn btn-danger">
                    <?php echo icon('xmark', 'svg-icon svg-sm'); ?> غیرفعال‌سازی ربات
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php reseller_layout_foot(); ?>
