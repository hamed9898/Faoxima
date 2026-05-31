<?php

require_once __DIR__ . '/layout.php';

$reseller = reseller_require_login();
$pdo = $GLOBALS['pdo'];
$rid = (int) $reseller['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    reseller_csrf_check();
    $action = (string) ($_POST['action'] ?? '');
    $serviceId = (int) ($_POST['service_id'] ?? 0);

    $sel = $pdo->prepare("SELECT * FROM reseller_service WHERE id = :id AND reseller_id = :rid LIMIT 1");
    $sel->execute([':id' => $serviceId, ':rid' => $rid]);
    $svc = $sel->fetch(PDO::FETCH_ASSOC);

    if (!$svc) {
        reseller_flash_set('error', 'سرویس یافت نشد.');
        header('Location: services.php');
        exit;
    }

    if ($action === 'delete') {
        require_once __DIR__ . '/../../function.php';
        require_once __DIR__ . '/../../botapi.php';
        require_once __DIR__ . '/../../panels.php';
        $ManagePanel = new ManagePanel();
        $ok = false;
        try {
            $res = $ManagePanel->RemoveUser((string) $svc['panel_name'], (string) $svc['username']);
            $ok = is_array($res) && (($res['status'] ?? '') === 'successful');
        } catch (\Throwable $e) {
            error_log('[reseller services delete] ' . $e->getMessage());
        }
        if ($ok) {
            $pdo->prepare("UPDATE reseller_service SET status = 'deleted' WHERE id = :id")
                ->execute([':id' => $serviceId]);
            reseller_flash_set('success', 'سرویس از پنل حذف شد.');
        } else {
            reseller_flash_set('error', 'حذف سرویس از پنل ناموفق بود.');
        }
        header('Location: services.php');
        exit;
    }
}

$filter = (string) ($_GET['status'] ?? 'all');
$where = "reseller_id = :rid";
$params = [':rid' => $rid];
if (in_array($filter, ['active', 'deleted'], true)) {
    $where .= " AND status = :st";
    $params[':st'] = $filter;
}
$stmt = $pdo->prepare("SELECT * FROM reseller_service WHERE $where ORDER BY id DESC LIMIT 300");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

reseller_layout_head('سرویس‌ها', 'services', $reseller);
?>
<div class="page-head">
    <div>
        <div class="page-head__title"><?php echo icon('package', 'svg-icon svg-lg'); ?> سرویس‌های من</div>
        <div class="page-head__sub">مدیریت سرویس‌های ساخته‌شده</div>
    </div>
    <div class="chip-row">
        <a href="services.php" class="chip<?php echo $filter === 'all' ? ' active' : ''; ?>"><span>همه</span></a>
        <a href="services.php?status=active" class="chip<?php echo $filter === 'active' ? ' active' : ''; ?>"><span>فعال</span></a>
        <a href="services.php?status=deleted" class="chip<?php echo $filter === 'deleted' ? ' active' : ''; ?>"><span>حذف‌شده</span></a>
    </div>
</div>

<?php reseller_flash_render(); ?>

<div class="card">
    <div class="table-wrap">
        <table class="app-table">
            <thead>
                <tr>
                    <th>نام کاربری</th>
                    <th>مشتری</th>
                    <th>حجم/مدت</th>
                    <th>قیمت</th>
                    <th>وضعیت</th>
                    <th>انقضا</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="7" style="text-align:center; padding:24px;">سرویسی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $s): ?>
                        <tr>
                            <td style="direction:ltr;"><?php echo reseller_e($s['username']); ?></td>
                            <td><?php echo reseller_e(($s['customer_name'] ?? '') !== '' ? $s['customer_name'] : '—'); ?></td>
                            <td style="direction:ltr;"><?php echo reseller_e($s['volume_gb']); ?>GB / <?php echo reseller_e($s['days']); ?>د</td>
                            <td style="direction:ltr;"><?php echo number_format((int) $s['price']); ?></td>
                            <td>
                                <span class="badge <?php echo $s['status'] === 'active' ? 'badge-success' : 'badge-gray'; ?>">
                                    <?php echo $s['status'] === 'active' ? 'فعال' : 'حذف‌شده'; ?>
                                </span>
                            </td>
                            <td><?php echo reseller_jdate($s['expire_at'], 'Y/m/d'); ?></td>
                            <td style="display:flex; gap:6px;">
                                <a href="subscription.php?token=<?php echo reseller_e($s['sub_token']); ?>" class="btn btn-sm btn-soft-info" target="_blank">
                                    <?php echo icon('eye', 'svg-icon svg-xs'); ?> اشتراک
                                </a>
                                <?php if ($s['status'] === 'active'): ?>
                                    <form method="post" action="services.php" onsubmit="return confirm('سرویس از پنل حذف شود؟ این عملیات قابل بازگشت نیست.');" style="display:inline;">
                                        <?php echo reseller_csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="service_id" value="<?php echo (int) $s['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-soft-danger"><?php echo icon('trash', 'svg-icon svg-xs'); ?> حذف</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
reseller_layout_foot();
