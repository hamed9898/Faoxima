<?php

/**
 * Shared bootstrap + helpers for the reseller panel (Phase 3).
 *
 * The reseller panel is a separate, self-service area that lives alongside the
 * admin panel. Resellers authenticate with their own credentials, top up a
 * wallet through the existing payment gateways, create/manage VPN services on
 * the panels the admin allows, and request USDT/TRON payouts.
 *
 * All money is stored as an integer amount of Toman on `reseller.balance`, and
 * every balance change is mirrored into the `reseller_ledger` accounting table.
 */

if (!defined('FAOXIMA_SKIP_BOTAPI_ROUTER')) {
    define('FAOXIMA_SKIP_BOTAPI_ROUTER', true);
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../lib/icons.php';
if (is_file(__DIR__ . '/../../jdf.php')) {
    require_once __DIR__ . '/../../jdf.php';
}

/* --------------------------------------------------------------------------
 * Settings
 * ------------------------------------------------------------------------ */

if (!function_exists('reseller_settings_row')) {
    function reseller_settings_row()
    {
        static $row = null;
        if ($row !== null) {
            return $row;
        }
        $row = [];
        $pdo = $GLOBALS['pdo'] ?? null;
        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->query("SELECT * FROM setting LIMIT 1");
                $r = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                if (is_array($r)) {
                    $row = $r;
                }
            } catch (\Throwable $e) {
            }
        }
        return $row;
    }
}

if (!function_exists('reseller_setting')) {
    function reseller_setting($key, $default = '')
    {
        $row = reseller_settings_row();
        return array_key_exists($key, $row) ? $row[$key] : $default;
    }
}

if (!function_exists('reseller_system_enabled')) {
    function reseller_system_enabled()
    {
        return (string) reseller_setting('reseller_system_status', '0') === '1';
    }
}

if (!function_exists('reseller_ip_allowed')) {
    /** Inherit the admin panel's IP allow-list (setting.iplogin). */
    function reseller_ip_allowed()
    {
        $raw = trim((string) reseller_setting('iplogin', ''));
        if ($raw === '' || $raw === '0' || $raw === '*' || $raw === 'all' || $raw === 'unlimited') {
            return true;
        }
        $list = [];
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            if (in_array('*', $decoded, true) || in_array('all', $decoded, true) || in_array('unlimited', $decoded, true)) {
                return true;
            }
            $list = $decoded;
        } elseif (filter_var($raw, FILTER_VALIDATE_IP)) {
            $list = [$raw];
        }
        if (!$list) {
            return true;
        }
        return in_array($_SERVER['REMOTE_ADDR'] ?? '', $list, true);
    }
}

/* --------------------------------------------------------------------------
 * CSRF
 * ------------------------------------------------------------------------ */

if (!function_exists('reseller_csrf_token')) {
    function reseller_csrf_token()
    {
        if (empty($_SESSION['reseller_csrf'])) {
            $_SESSION['reseller_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['reseller_csrf'];
    }
}

if (!function_exists('reseller_csrf_check')) {
    function reseller_csrf_check()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $incoming = $_POST['_csrf'] ?? '';
        if (!is_string($incoming) || !hash_equals((string) ($_SESSION['reseller_csrf'] ?? ''), $incoming)) {
            http_response_code(403);
            exit('درخواست نامعتبر — توکن CSRF اشتباه است');
        }
    }
}

/* --------------------------------------------------------------------------
 * Authentication
 * ------------------------------------------------------------------------ */

if (!function_exists('reseller_find_by_username')) {
    function reseller_find_by_username($username)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return null;
        }
        $stmt = $pdo->prepare("SELECT * FROM reseller WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('reseller_find_by_id')) {
    function reseller_find_by_id($id)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return null;
        }
        $stmt = $pdo->prepare("SELECT * FROM reseller WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('reseller_current')) {
    /** Returns the logged-in reseller row (fresh from DB) or null. */
    function reseller_current()
    {
        if (empty($_SESSION['reseller_id'])) {
            return null;
        }
        $row = reseller_find_by_id($_SESSION['reseller_id']);
        if (!$row || (string) ($row['status'] ?? '') !== 'active') {
            return null;
        }
        return $row;
    }
}

if (!function_exists('reseller_require_login')) {
    function reseller_require_login()
    {
        $r = reseller_current();
        if (!$r) {
            header('Location: login.php');
            exit;
        }
        if (!reseller_ip_allowed()) {
            $_SESSION = [];
            header('Location: login.php');
            exit;
        }
        return $r;
    }
}

if (!function_exists('reseller_verify_password')) {
    function reseller_verify_password($plain, $hash)
    {
        $plain = (string) $plain;
        $hash = (string) $hash;
        if ($hash === '') {
            return false;
        }
        // Stored as password_hash() output; tolerate a legacy plaintext value.
        if (password_verify($plain, $hash)) {
            return true;
        }
        if (!preg_match('/^\$(2y|argon)/', $hash) && hash_equals($hash, $plain)) {
            return true;
        }
        return false;
    }
}

/* --------------------------------------------------------------------------
 * Wallet + accounting ledger
 * ------------------------------------------------------------------------ */

if (!function_exists('reseller_wallet_apply')) {
    /**
     * Atomically change a reseller's balance and record a ledger entry.
     *
     * @param int    $resellerId
     * @param string $type   topup|purchase|withdraw|withdraw_refund|admin_credit|admin_debit
     * @param int    $amount Signed Toman (positive = credit, negative = debit).
     * @param string $description
     * @param string $ref
     * @param bool   $allowNegative Permit the balance to drop below zero.
     * @return array ['ok'=>bool,'balance'=>int,'msg'=>string]
     */
    function reseller_wallet_apply($resellerId, $type, $amount, $description = '', $ref = '', $allowNegative = false)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return ['ok' => false, 'balance' => 0, 'msg' => 'no database'];
        }
        $resellerId = (int) $resellerId;
        $amount = (int) round((float) $amount);
        try {
            $pdo->beginTransaction();
            $sel = $pdo->prepare("SELECT balance FROM reseller WHERE id = :id FOR UPDATE");
            $sel->execute([':id' => $resellerId]);
            $cur = $sel->fetchColumn();
            if ($cur === false) {
                $pdo->rollBack();
                return ['ok' => false, 'balance' => 0, 'msg' => 'reseller not found'];
            }
            $cur = (int) $cur;
            $new = $cur + $amount;
            if ($new < 0 && !$allowNegative) {
                $pdo->rollBack();
                return ['ok' => false, 'balance' => $cur, 'msg' => 'موجودی کافی نیست'];
            }
            $upd = $pdo->prepare("UPDATE reseller SET balance = :b WHERE id = :id");
            $upd->execute([':b' => $new, ':id' => $resellerId]);
            $ins = $pdo->prepare(
                "INSERT INTO reseller_ledger (reseller_id, type, amount, balance_after, description, ref, created_at)
                 VALUES (:rid, :type, :amount, :bal, :desc, :ref, :ts)"
            );
            $ins->execute([
                ':rid'    => $resellerId,
                ':type'   => $type,
                ':amount' => $amount,
                ':bal'    => $new,
                ':desc'   => mb_substr((string) $description, 0, 490),
                ':ref'    => mb_substr((string) $ref, 0, 180),
                ':ts'     => (string) time(),
            ]);
            $pdo->commit();
            return ['ok' => true, 'balance' => $new, 'msg' => ''];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[reseller_wallet_apply] ' . $e->getMessage());
            return ['ok' => false, 'balance' => 0, 'msg' => 'خطای پایگاه داده'];
        }
    }
}

/* --------------------------------------------------------------------------
 * Products available to a reseller
 * ------------------------------------------------------------------------ */

if (!function_exists('reseller_allowed_products')) {
    /**
     * Products a reseller may sell: admin-flagged (reseller_status='1') AND, when
     * the reseller has an explicit allowed_products whitelist, restricted to it.
     */
    function reseller_allowed_products(array $reseller)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return [];
        }
        $stmt = $pdo->query("SELECT * FROM product WHERE reseller_status = '1' ORDER BY id DESC");
        $all = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        $whitelist = [];
        $raw = trim((string) ($reseller['allowed_products'] ?? ''));
        if ($raw !== '' && $raw !== '[]') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $whitelist = array_map('strval', $decoded);
            }
        }
        if (!$whitelist) {
            return $all;
        }
        return array_values(array_filter($all, static function ($p) use ($whitelist) {
            return in_array((string) ($p['code_product'] ?? ''), $whitelist, true);
        }));
    }
}

if (!function_exists('reseller_product_price')) {
    /** Price charged to the reseller wallet for a product (reseller_price overrides). */
    function reseller_product_price(array $product)
    {
        $rp = trim((string) ($product['reseller_price'] ?? ''));
        if ($rp !== '' && is_numeric($rp)) {
            return (int) round((float) $rp);
        }
        return (int) round((float) ($product['price_product'] ?? 0));
    }
}

if (!function_exists('reseller_min_withdraw')) {
    function reseller_min_withdraw(array $reseller)
    {
        $perReseller = trim((string) ($reseller['min_withdraw'] ?? ''));
        if ($perReseller !== '' && is_numeric($perReseller)) {
            return (int) $perReseller;
        }
        $global = trim((string) reseller_setting('reseller_min_withdraw', '500000'));
        return is_numeric($global) ? (int) $global : 500000;
    }
}

/* --------------------------------------------------------------------------
 * Presentation helpers
 * ------------------------------------------------------------------------ */

if (!function_exists('reseller_money')) {
    function reseller_money($amount)
    {
        return number_format((float) $amount) . ' تومان';
    }
}

if (!function_exists('reseller_jdate')) {
    function reseller_jdate($timestamp, $format = 'Y/m/d H:i')
    {
        $ts = (int) $timestamp;
        if ($ts <= 0) {
            return '—';
        }
        if (function_exists('jdate')) {
            return jdate($format, $ts);
        }
        return date($format, $ts);
    }
}

if (!function_exists('reseller_e')) {
    function reseller_e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('reseller_count_active_services')) {
    function reseller_count_active_services($resellerId)
    {
        $pdo = $GLOBALS['pdo'] ?? null;
        if (!($pdo instanceof PDO)) {
            return 0;
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reseller_service WHERE reseller_id = :id AND status = 'active'");
        $stmt->execute([':id' => (int) $resellerId]);
        return (int) $stmt->fetchColumn();
    }
}
