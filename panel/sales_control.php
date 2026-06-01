<?php


if (!defined('FAOXIMA_SKIP_BOTAPI_ROUTER')) {
    define('FAOXIMA_SKIP_BOTAPI_ROUTER', true);
}

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../botapi.php';
require_once __DIR__ . '/lib/icons.php';
require_once __DIR__ . '/../jdf.php';


$sessionUser = isset($_SESSION['user']) && is_string($_SESSION['user']) && $_SESSION['user'] !== ''
    ? $_SESSION['user']
    : null;
if ($sessionUser === null) {
    header('Location: login.php');
    exit;
}
$query = $pdo->prepare("SELECT * FROM admin WHERE username = :username LIMIT 1");
$query->bindValue(':username', $sessionUser, PDO::PARAM_STR);
$query->execute();
$adminRow = $query->fetch(PDO::FETCH_ASSOC);
if (!$adminRow) {
    $_SESSION = [];
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$_csrf = $_SESSION['csrf_token'];

function sc_e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

/* Live (sold / in-service) invoice statuses — same set the dashboard uses. */
const SC_LIVE_STATUSES = "('active','end_of_time','end_of_volume','sendedwarn','send_on_hold')";

/* Sales-behaviour toggles actually read by the bot (mirrors panel/shopsettings.php). */
$SC_TOGGLES = [
    ['name' => 'statusdirectpabuy',  'label' => 'پرداخت مستقیم (بدون شارژ کیف پول)', 'on' => 'ondirectbuy',  'off' => 'offdirectbuy'],
    ['name' => 'statusshowprice',    'label' => 'نمایش قیمت روی محصولات',            'on' => 'onshowprice',  'off' => 'offshowprice'],
    ['name' => 'statusextra',        'label' => 'خرید حجم اضافه',                    'on' => 'onextra',      'off' => 'offextra'],
    ['name' => 'statustimeextra',    'label' => 'خرید زمان اضافه',                   'on' => 'ontimeextraa', 'off' => 'offtimeextraa'],
    ['name' => 'statuschangeservice','label' => 'اجازه‌ی تغییر سرویس',               'on' => 'onstatus',     'off' => 'offstatus'],
    ['name' => 'configshow',         'label' => 'نمایش کانفیگ به کاربر',             'on' => 'onconfig',     'off' => 'offconfig'],
    ['name' => 'statusdisorder',     'label' => 'دکمه گزارش اختلال در منوی سرویس',   'on' => 'ondisorder',   'off' => 'offdisorder'],
];

function sc_shop_get(PDO $pdo, string $key): string {
    try {
        $st = $pdo->prepare("SELECT value FROM shopSetting WHERE Namevalue = :k LIMIT 1");
        $st->execute([':k' => $key]);
        $v = $st->fetchColumn();
        return $v === false ? '' : (string)$v;
    } catch (\Throwable $e) {
        return '';
    }
}

function sc_shop_set(PDO $pdo, string $key, string $value): bool {
    // UPDATE-then-INSERT so it works even if Namevalue has no unique key.
    try {
        $up = $pdo->prepare("UPDATE shopSetting SET value = :v WHERE Namevalue = :k");
        $up->execute([':v' => $value, ':k' => $key]);
        if ($up->rowCount() > 0) return true;
        $ex = $pdo->prepare("SELECT 1 FROM shopSetting WHERE Namevalue = :k LIMIT 1");
        $ex->execute([':k' => $key]);
        if ($ex->fetchColumn() !== false) return true; // already equals value
        $in = $pdo->prepare("INSERT INTO shopSetting (Namevalue, value) VALUES (:k, :v)");
        $in->execute([':k' => $key, ':v' => $value]);
        return true;
    } catch (\Throwable $e) {
        error_log('[sales_control] shop_set ' . $key . ' failed: ' . $e->getMessage());
        return false;
    }
}

$flash = null; // ['type'=>'success|error', 'msg'=>...]

/* ───────────────────────── POST actions (CSRF-protected) ───────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], (string)($_POST['_csrf'] ?? ''))) {
        http_response_code(400);
        exit('Bad CSRF token');
    }
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'save_toggles') {
        $saved = 0;
        foreach ($SC_TOGGLES as $t) {
            $new = isset($_POST['f_' . $t['name']]) ? $t['on'] : $t['off'];
            if (sc_shop_get($pdo, $t['name']) === $new) continue;
            if (sc_shop_set($pdo, $t['name'], $new)) $saved++;
        }
        $_SESSION['_sc_flash'] = ['type' => 'success', 'msg' => "کنترل‌های فروش ذخیره شد ($saved مورد به‌روزرسانی شد)."];
        header('Location: sales_control.php');
        exit;
    }

    if ($action === 'set_webhook') {
        $url = trim((string)($_POST['webhook_url'] ?? ''));
        if ($APIKEY === '') {
            $_SESSION['_sc_flash'] = ['type' => 'error', 'msg' => 'توکن ربات اصلی (APIKEY) در config.php تنظیم نشده است.'];
        } elseif (!preg_match('#^https://#i', $url)) {
            $_SESSION['_sc_flash'] = ['type' => 'error', 'msg' => 'آدرس وب‌هوک باید با https:// شروع شود.'];
        } else {
            $res = telegram('setWebhook', [
                'url'             => $url,
                'max_connections' => 40,
                'allowed_updates' => json_encode(['message', 'callback_query', 'pre_checkout_query']),
            ], $APIKEY);
            if (is_array($res) && !empty($res['ok'])) {
                $_SESSION['_sc_flash'] = ['type' => 'success', 'msg' => 'وب‌هوک ربات اصلی با موفقیت روی این آدرس ثبت شد.'];
            } else {
                $desc = is_array($res) ? (string)($res['description'] ?? 'نامشخص') : 'نامشخص';
                $_SESSION['_sc_flash'] = ['type' => 'error', 'msg' => 'ثبت وب‌هوک ناموفق بود: ' . $desc];
            }
        }
        header('Location: sales_control.php');
        exit;
    }

    if ($action === 'test_message') {
        if ($APIKEY === '' || $adminnumber === '') {
            $_SESSION['_sc_flash'] = ['type' => 'error', 'msg' => 'توکن ربات یا شناسه‌ی ادمین در config.php تنظیم نشده است.'];
        } else {
            $txt = "✅ پیام تست از «مرکز کنترل فروش»\n🕒 " . jdate('Y/m/d H:i:s') . "\nاتصال پنل مدیریت به ربات اصلی سالم است.";
            $res = telegram('sendMessage', ['chat_id' => $adminnumber, 'text' => $txt], $APIKEY);
            if (is_array($res) && !empty($res['ok'])) {
                $_SESSION['_sc_flash'] = ['type' => 'success', 'msg' => 'پیام تست به ادمین ارسال شد.'];
            } else {
                $desc = is_array($res) ? (string)($res['description'] ?? 'نامشخص') : 'نامشخص';
                $_SESSION['_sc_flash'] = ['type' => 'error', 'msg' => 'ارسال پیام تست ناموفق بود: ' . $desc];
            }
        }
        header('Location: sales_control.php');
        exit;
    }
}

if (!empty($_SESSION['_sc_flash'])) {
    $flash = $_SESSION['_sc_flash'];
    unset($_SESSION['_sc_flash']);
}

/* ───────────────────────── Sales KPIs ───────────────────────── */
@set_time_limit(20);
$now = time();
$startToday = strtotime('today 00:00:00');
$start7d    = $now - 7  * 86400;
$start30d   = $now - 30 * 86400;
$start24h   = $now - 86400;

function sc_window(PDO $pdo, int $since): array {
    $sql = "SELECT COUNT(*) AS c, COALESCE(SUM(price_product),0) AS s
              FROM invoice
             WHERE time_sell >= :t
               AND status IN " . SC_LIVE_STATUSES . "
               AND name_product != 'سرویس تست'";
    try {
        $st = $pdo->prepare($sql);
        $st->bindValue(':t', $since, PDO::PARAM_INT);
        $st->execute();
        $r = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return ['count' => (int)($r['c'] ?? 0), 'sum' => (float)($r['s'] ?? 0)];
    } catch (\Throwable $e) {
        return ['count' => 0, 'sum' => 0.0];
    }
}

$kpiToday = sc_window($pdo, $startToday);
$kpi7d    = sc_window($pdo, $start7d);
$kpi30d   = sc_window($pdo, $start30d);

try {
    $totalRow = $pdo->query(
        "SELECT COUNT(*) AS c, COALESCE(SUM(price_product),0) AS s FROM invoice
          WHERE status IN " . SC_LIVE_STATUSES . " AND name_product != 'سرویس تست'"
    )->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) { $totalRow = []; }
$totalOrders  = (int)($totalRow['c'] ?? 0);
$totalRevenue = (float)($totalRow['s'] ?? 0);

try {
    $activeServices = (int)$pdo->query(
        "SELECT COUNT(*) FROM invoice WHERE status = 'active' AND name_product != 'سرویس تست'"
    )->fetchColumn();
} catch (\Throwable $e) { $activeServices = 0; }

try {
    $st = $pdo->prepare("SELECT COUNT(*) FROM user WHERE register > :t AND register != 'none'");
    $st->bindValue(':t', $start24h, PDO::PARAM_INT);
    $st->execute();
    $newUsers24h = (int)$st->fetchColumn();
} catch (\Throwable $e) { $newUsers24h = 0; }

try {
    $walletLiability = (float)$pdo->query("SELECT COALESCE(SUM(GREATEST(0, Balance)),0) FROM user")->fetchColumn();
} catch (\Throwable $e) {
    try { $walletLiability = (float)$pdo->query("SELECT COALESCE(SUM(Balance),0) FROM user")->fetchColumn(); }
    catch (\Throwable $e2) { $walletLiability = 0.0; }
}

try {
    $st = $pdo->prepare(
        "SELECT name_product, COUNT(*) AS c, COALESCE(SUM(price_product),0) AS s
           FROM invoice
          WHERE time_sell >= :t AND status IN " . SC_LIVE_STATUSES . " AND name_product != 'سرویس تست'
          GROUP BY name_product ORDER BY c DESC LIMIT 6"
    );
    $st->bindValue(':t', $start30d, PDO::PARAM_INT);
    $st->execute();
    $topProducts = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) { $topProducts = []; }

try {
    $recentTx = $pdo->query(
        "SELECT id_user, id_order, price, time, Payment_Method, payment_Status
           FROM Payment_report ORDER BY time DESC LIMIT 8"
    )->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) { $recentTx = []; }

/* ───────────────────────── Main bot status ───────────────────────── */
$botConfigured = ($APIKEY !== '');
$botMe = null; $botErr = ''; $webhookInfo = null;
if ($botConfigured) {
    try {
        $me = telegram('getMe', [], $APIKEY);
        if (is_array($me) && !empty($me['ok'])) { $botMe = $me['result']; }
        else { $botErr = is_array($me) ? (string)($me['description'] ?? 'پاسخ نامعتبر') : 'پاسخ نامعتبر'; }
    } catch (\Throwable $e) { $botErr = $e->getMessage(); }
    try {
        $wi = telegram('getWebhookInfo', [], $APIKEY);
        if (is_array($wi) && !empty($wi['ok'])) { $webhookInfo = $wi['result']; }
    } catch (\Throwable $e) { /* ignore */ }
}
$currentWebhook = is_array($webhookInfo) ? (string)($webhookInfo['url'] ?? '') : '';
$suggestedWebhook = $currentWebhook !== ''
    ? $currentWebhook
    : ((defined('APP_ORIGIN') ? APP_ORIGIN : ($domainhosts !== '' ? 'https://' . $domainhosts : '')) . '/index.php');

function sc_money($v): string { return number_format((float)$v) . ' تومان'; }
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>مرکز کنترل فروش | پنل فاکسیما</title>
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/admin-extra.css">
    <script src="js/theme.js" defer></script>
</head>
<body>

<section id="container">
    <?php include("header.php"); ?>

    <section id="main-content">
        <div class="wrapper">

            <div class="page-head">
                <div>
                    <div class="page-head__title">
                        <?php echo icon('bolt', 'svg-icon svg-lg'); ?>
                        مرکز کنترل فروش
                    </div>
                    <div class="page-head__sub">کنترل کامل عملیات فروش و اتصال به ربات اصلی</div>
                </div>
                <div class="chip-row">
                    <a href="index.php" class="chip"><?php echo icon('home', 'svg-icon svg-sm'); ?><span>داشبورد</span></a>
                    <a href="invoice.php" class="chip"><?php echo icon('dollar-sign', 'svg-icon svg-sm'); ?><span>سفارشات</span></a>
                    <a href="shopsettings.php" class="chip"><?php echo icon('package', 'svg-icon svg-sm'); ?><span>قابلیت‌های فروشگاه</span></a>
                </div>
            </div>

            <?php if ($flash !== null): ?>
            <div class="alert <?php echo $flash['type'] === 'success' ? 'alert-success' : 'alert-danger'; ?>" style="margin-bottom:16px;">
                <?php echo icon($flash['type'] === 'success' ? 'circle-check' : 'circle-exclamation', 'svg-icon svg-sm'); ?>
                <span><?php echo sc_e($flash['msg']); ?></span>
            </div>
            <?php endif; ?>

            <!-- KPI cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card__icon icon-green"><?php echo icon('money-bill', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($kpiToday['sum']); ?> <small>تومان</small></span>
                        <span class="stat-card__label">فروش امروز (<?php echo number_format($kpiToday['count']); ?> سفارش)</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon icon-blue"><?php echo icon('chart-line', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($kpi7d['sum']); ?> <small>تومان</small></span>
                        <span class="stat-card__label">فروش ۷ روز (<?php echo number_format($kpi7d['count']); ?> سفارش)</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon icon-purple"><?php echo icon('receipt', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($kpi30d['sum']); ?> <small>تومان</small></span>
                        <span class="stat-card__label">فروش ۳۰ روز (<?php echo number_format($kpi30d['count']); ?> سفارش)</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon icon-rose"><?php echo icon('cart-shopping', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($totalRevenue); ?> <small>تومان</small></span>
                        <span class="stat-card__label">فروش کل (<?php echo number_format($totalOrders); ?> سفارش)</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon icon-blue"><?php echo icon('antenna', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($activeServices); ?></span>
                        <span class="stat-card__label">سرویس‌های فعال</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon icon-purple"><?php echo icon('user-plus', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($newUsers24h); ?></span>
                        <span class="stat-card__label">کاربران جدید (۲۴ ساعت)</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card__icon icon-green"><?php echo icon('wallet', 'svg-icon'); ?></div>
                    <div class="stat-card__info">
                        <span class="stat-card__value"><?php echo number_format($walletLiability); ?> <small>تومان</small></span>
                        <span class="stat-card__label">بدهی کیف‌پول کاربران</span>
                    </div>
                </div>
            </div>

            <div class="grid-2" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:16px; margin-top:16px;">

                <!-- Quick sales controls -->
                <div class="card">
                    <div class="card__head">
                        <div class="card__title"><?php echo icon('bolt', 'svg-icon svg-md'); ?><span>کنترل‌های سریع فروش</span></div>
                    </div>
                    <p class="text-muted" style="margin:0 0 8px; font-size:13px;">این کلیدها مستقیماً رفتار ربات فروش را کنترل می‌کنند.</p>
                    <form method="post" action="sales_control.php">
                        <input type="hidden" name="_csrf" value="<?php echo sc_e($_csrf); ?>">
                        <input type="hidden" name="action" value="save_toggles">
                        <?php foreach ($SC_TOGGLES as $t):
                            $cur = sc_shop_get($pdo, $t['name']);
                            $isOn = ($cur === $t['on']);
                            $idAttr = 'f_' . sc_e($t['name']);
                        ?>
                        <div class="setting-row">
                            <label for="<?php echo $idAttr; ?>" class="setting-row__label"><?php echo sc_e($t['label']); ?></label>
                            <div class="setting-row__control">
                                <label class="switch" title="<?php echo sc_e($t['name']); ?>">
                                    <input type="checkbox" id="<?php echo $idAttr; ?>" name="<?php echo $idAttr; ?>" value="1" <?php echo $isOn ? 'checked' : ''; ?>>
                                    <span class="switch__slot"></span>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="save-bar" style="margin-top:16px;">
                            <button type="submit" class="btn btn-primary">
                                <?php echo icon('save', 'svg-icon svg-sm'); ?> ذخیره کنترل‌ها
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Main bot connection -->
                <div class="card">
                    <div class="card__head">
                        <div class="card__title"><?php echo icon('robot', 'svg-icon svg-md'); ?><span>اتصال ربات اصلی فروش</span></div>
                    </div>
                    <?php if (!$botConfigured): ?>
                        <div class="alert alert-danger">
                                <?php echo icon('circle-exclamation', 'svg-icon svg-sm'); ?>
                                <span>توکن ربات اصلی (<code>$APIKEY</code>) در <code>config.php</code> تنظیم نشده است.</span>
                            </div>
                        <?php else: ?>
                            <ul class="kv-list" style="list-style:none; padding:0; margin:0 0 14px; font-size:13px;">
                                <li style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid var(--border-soft,#2a2a35);">
                                    <span class="text-muted">وضعیت ربات</span>
                                    <?php if ($botMe): ?>
                                        <span class="badge badge-success">آنلاین · @<?php echo sc_e($botMe['username'] ?? ''); ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">خطا: <?php echo sc_e($botErr); ?></span>
                                    <?php endif; ?>
                                </li>
                                <li style="display:flex; justify-content:space-between; gap:8px; padding:7px 0; border-bottom:1px solid var(--border-soft,#2a2a35);">
                                    <span class="text-muted">وب‌هوک فعلی</span>
                                    <span style="direction:ltr; word-break:break-all; text-align:left; max-width:62%;"><?php echo $currentWebhook !== '' ? sc_e($currentWebhook) : '—'; ?></span>
                                </li>
                                <li style="display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid var(--border-soft,#2a2a35);">
                                    <span class="text-muted">آپدیت‌های در صف</span>
                                    <span class="badge badge-info"><?php echo number_format((int)(is_array($webhookInfo) ? ($webhookInfo['pending_update_count'] ?? 0) : 0)); ?></span>
                                </li>
                                <?php if (is_array($webhookInfo) && !empty($webhookInfo['last_error_message'])): ?>
                                <li style="display:flex; justify-content:space-between; gap:8px; padding:7px 0;">
                                    <span class="text-muted">آخرین خطای وب‌هوک</span>
                                    <span class="badge badge-gray" style="direction:ltr; text-align:left; max-width:62%; white-space:normal;"><?php echo sc_e($webhookInfo['last_error_message']); ?></span>
                                </li>
                                <?php endif; ?>
                            </ul>

                            <form method="post" action="sales_control.php" style="margin-bottom:10px;">
                                <input type="hidden" name="_csrf" value="<?php echo sc_e($_csrf); ?>">
                                <input type="hidden" name="action" value="set_webhook">
                                <label style="display:block; font-size:13px; margin-bottom:6px;">آدرس وب‌هوک</label>
                                <input type="text" name="webhook_url" dir="ltr" value="<?php echo sc_e($suggestedWebhook); ?>"
                                       style="width:100%; box-sizing:border-box; margin-bottom:10px;" placeholder="https://example.com/index.php">
                                <button type="submit" class="btn btn-outline">
                                    <?php echo icon('link', 'svg-icon svg-sm'); ?> ثبت / به‌روزرسانی وب‌هوک
                                </button>
                            </form>

                            <form method="post" action="sales_control.php">
                                <input type="hidden" name="_csrf" value="<?php echo sc_e($_csrf); ?>">
                                <input type="hidden" name="action" value="test_message">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo icon('paper-plane', 'svg-icon svg-sm'); ?> ارسال پیام تست به ادمین
                                </button>
                            </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid-2" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:16px; margin-top:16px;">

                <!-- Top products -->
                <div class="card">
                    <div class="card__head">
                        <div class="card__title"><?php echo icon('chart-bar', 'svg-icon svg-md'); ?><span>پرفروش‌ترین محصولات (۳۰ روز)</span></div>
                    </div>
                    <div class="table-wrap">
                            <table class="app-table" style="width:100%;">
                                <thead><tr><th>محصول</th><th>تعداد</th><th>درآمد</th></tr></thead>
                                <tbody>
                                <?php if (!$topProducts): ?>
                                    <tr><td colspan="3" class="text-muted" style="text-align:center;">داده‌ای موجود نیست</td></tr>
                                <?php else: foreach ($topProducts as $p): ?>
                                    <tr>
                                        <td data-label="محصول"><?php echo sc_e($p['name_product']); ?></td>
                                        <td data-label="تعداد"><?php echo number_format((int)$p['c']); ?></td>
                                        <td data-label="درآمد"><?php echo number_format((float)$p['s']); ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                    </div>
                </div>

                <!-- Recent transactions -->
                <div class="card">
                    <div class="card__head">
                        <div class="card__title"><?php echo icon('wallet', 'svg-icon svg-md'); ?><span>آخرین تراکنش‌ها</span></div>
                    </div>
                    <div class="table-wrap">
                            <table class="app-table" style="width:100%;">
                                <thead><tr><th>کاربر</th><th>مبلغ</th><th>وضعیت</th><th>زمان</th></tr></thead>
                                <tbody>
                                <?php if (!$recentTx): ?>
                                    <tr><td colspan="4" class="text-muted" style="text-align:center;">تراکنشی موجود نیست</td></tr>
                                <?php else: foreach ($recentTx as $tx):
                                    $paid = ((string)($tx['payment_Status'] ?? '') === 'paid');
                                    $tt = (int)($tx['time'] ?? 0);
                                ?>
                                    <tr>
                                        <td data-label="کاربر"><code><?php echo sc_e($tx['id_user']); ?></code></td>
                                        <td data-label="مبلغ"><?php echo number_format((float)($tx['price'] ?? 0)); ?></td>
                                        <td data-label="وضعیت"><span class="badge <?php echo $paid ? 'badge-success' : 'badge-gray'; ?>"><?php echo $paid ? 'پرداخت شده' : sc_e($tx['payment_Status'] ?? '—'); ?></span></td>
                                        <td data-label="زمان"><?php echo $tt > 0 ? sc_e(jdate('Y/m/d H:i', $tt)) : '—'; ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</section>

<div class="sidebar-overlay"></div>

</body>
</html>
