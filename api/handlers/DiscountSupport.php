<?php


declare(strict_types=1);

require_once __DIR__ . '/../lib/Db.php';

if (class_exists('MiniDiscount')) {
    return;
}

final class MiniDiscount
{
    public static function redeemGift(string $code, array $user): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['ok' => false, 'reason' => '❌ کد هدیه را وارد کنید.'];
        }

        $row = FaoximaDb::fetchOne('SELECT * FROM Discount WHERE code = :c LIMIT 1', [':c' => $code]);
        if ($row === null) {
            return ['ok' => false, 'reason' => '❌ کد هدیه نامعتبر است.'];
        }

        $limitUse  = (int)($row['limituse'] ?? 0);
        $limitUsed = (int)($row['limitused'] ?? 0);
        if ($limitUse > 0 && $limitUsed >= $limitUse) {
            return ['ok' => false, 'reason' => '❌ ظرفیت استفاده از این کد هدیه به پایان رسیده است.'];
        }

        $already = (int) FaoximaDb::fetchScalar(
            'SELECT COUNT(*) FROM Giftcodeconsumed WHERE id_user = :u AND code = :c',
            [':u' => (string)$user['id'], ':c' => $code]
        );
        if ($already > 0) {
            return ['ok' => false, 'reason' => '❌ شما قبلاً از این کد هدیه استفاده کرده‌اید.'];
        }

        $amount = (int) round((float)($row['price'] ?? 0));
        if ($amount <= 0) {
            return ['ok' => false, 'reason' => '❌ این کد هدیه مبلغی ندارد.'];
        }

        $credited = balance_atomic_credit($user['id'], $amount);
        if ($credited === false) {
            return ['ok' => false, 'reason' => '❌ خطا در واریز هدیه. لطفاً دوباره تلاش کنید.'];
        }

        update('Discount', 'limitused', (string)($limitUsed + 1), 'code', $code);

        try {
            $pdo = FaoximaDb::pdo();
            $pdo->prepare('INSERT INTO Giftcodeconsumed (id_user, code) VALUES (:u, :c)')
                ->execute([':u' => (string)$user['id'], ':c' => $code]);
        } catch (Throwable $e) {
        }

        $newBalance = FaoximaDb::fetchScalar('SELECT Balance FROM user WHERE id = :u', [':u' => $user['id']]);
        $newBalance = $newBalance === null ? ((float)($user['Balance'] ?? 0) + $amount) : (float)$newBalance;

        return ['ok' => true, 'amount' => $amount, 'new_balance' => $newBalance];
    }

    public static function validateSell(string $code, string $type, string $codeProduct, string $codePanel, array $user): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['ok' => false, 'reason' => '❌ کد تخفیف را وارد کنید.'];
        }

        $agent = (string)($user['agent'] ?? 'f');
        $typeClause = in_array($type, ['buy', 'extend'], true) ? $type : 'all';
        if ($codeProduct === '') $codeProduct = 'all';
        if ($codePanel === '')   $codePanel = '/all';

        $row = FaoximaDb::fetchOne(
            "SELECT * FROM DiscountSell
              WHERE codeDiscount = :code
                AND (code_product = :cp OR code_product = 'all')
                AND (code_panel = :cpan OR code_panel = '/all')
                AND (agent = :agent OR agent = 'allusers')
                AND (type = 'all' OR type = :type)
              LIMIT 1",
            [':code' => $code, ':cp' => $codeProduct, ':cpan' => $codePanel, ':agent' => $agent, ':type' => $typeClause]
        );
        if ($row === null) {
            return ['ok' => false, 'reason' => '❌ کد تخفیف نامعتبر است.'];
        }

        $expiry = (int)($row['time'] ?? 0);
        if ($expiry !== 0 && time() >= $expiry) {
            return ['ok' => false, 'reason' => '❌ زمان کد تخفیف به پایان رسیده است.'];
        }

        if ((int)($row['limitDiscount'] ?? 0) <= (int)($row['usedDiscount'] ?? 0)) {
            return ['ok' => false, 'reason' => '❌ ظرفیت استفاده از این کد تخفیف به پایان رسیده است.'];
        }

        $usedByUser = (int) FaoximaDb::fetchScalar(
            'SELECT COUNT(*) FROM Giftcodeconsumed WHERE id_user = :u AND code = :c',
            [':u' => (string)$user['id'], ':c' => $code]
        );
        $useUser = (int)($row['useuser'] ?? 0);
        if ($useUser > 0 && $usedByUser >= $useUser) {
            return ['ok' => false, 'reason' => '⭕️ سقف استفاده شما از این کد تخفیف پر شده است.'];
        }

        if ((string)($row['usefirst'] ?? '') === '1') {
            $invoiceCount = (int) FaoximaDb::fetchScalar(
                'SELECT COUNT(*) FROM invoice WHERE id_user = :u',
                [':u' => (string)$user['id']]
            );
            if ($invoiceCount != 0) {
                return ['ok' => false, 'reason' => '❌ این کد تخفیف فقط برای اولین خرید قابل استفاده است.'];
            }
        }

        $percent = (int) round((float)($row['price'] ?? 0));
        if ($percent <= 0 || $percent > 100) {
            return ['ok' => false, 'reason' => '❌ درصد کد تخفیف نامعتبر است.'];
        }

        return ['ok' => true, 'percent' => $percent, 'code' => $code];
    }

    public static function markSellUsed(string $code, array $user): void
    {
        $code = trim($code);
        if ($code === '') return;
        try {
            $pdo = FaoximaDb::pdo();
            $row = FaoximaDb::fetchOne('SELECT usedDiscount FROM DiscountSell WHERE codeDiscount = :c LIMIT 1', [':c' => $code]);
            $used = $row !== null ? ((int)($row['usedDiscount'] ?? 0) + 1) : 1;
            $pdo->prepare('UPDATE DiscountSell SET usedDiscount = :v WHERE codeDiscount = :c')
                ->execute([':v' => (string)$used, ':c' => $code]);
            $pdo->prepare('INSERT INTO Giftcodeconsumed (id_user, code) VALUES (:u, :c)')
                ->execute([':u' => (string)$user['id'], ':c' => $code]);
        } catch (Throwable $e) {
        }
    }
}
