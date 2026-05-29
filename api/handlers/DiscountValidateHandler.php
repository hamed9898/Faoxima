<?php


declare(strict_types=1);

require_once __DIR__ . '/BaseHandler.php';
require_once __DIR__ . '/DiscountSupport.php';

final class DiscountValidateHandler extends BaseHandler
{
    public function handle(): void
    {
        $this->requireMethod('POST');

        $code = FaoximaInput::string($this->data, 'code');
        if ($code === '') {
            FaoximaResponse::badRequest('code is required');
        }

        $typeIn = FaoximaInput::string($this->data, 'context');
        $type = in_array($typeIn, ['buy', 'extend'], true) ? $typeIn : 'all';

        $username = FaoximaInput::nullableString($this->data, 'username');
        $codeProduct = FaoximaInput::string($this->data, 'product_code');
        $codePanel   = FaoximaInput::string($this->data, 'code_panel');

        if (($codeProduct === '' || $codePanel === '') && $username !== null && $username !== '') {
            $invoice = FaoximaDb::fetchOne(
                'SELECT * FROM invoice WHERE id_user = :u AND username = :n LIMIT 1',
                [':u' => $this->user['id'], ':n' => $username]
            );
            if (is_array($invoice)) {
                $panel = select('marzban_panel', '*', 'name_panel', $invoice['Service_location'], 'select');
                if ($codePanel === '' && is_array($panel)) {
                    $codePanel = (string)($panel['code_panel'] ?? '');
                }
            }
        }

        $result = MiniDiscount::validateSell($code, $type, $codeProduct, $codePanel, $this->user);
        if (empty($result['ok'])) {
            FaoximaResponse::fail(422, (string)($result['reason'] ?? '❌ کد تخفیف نامعتبر است.'));
        }

        $percent = (int)$result['percent'];
        $base = FaoximaInput::int($this->data, 'base_price', 0);
        $finalPrice = $base > 0 ? (int) round($base - (($base * $percent) / 100)) : null;

        FaoximaResponse::ok([
            'code'        => (string)$result['code'],
            'percent'     => $percent,
            'base_price'  => $base > 0 ? $base : null,
            'final_price' => $finalPrice,
            'message'     => "🤩 کد تخفیف معتبر است؛ {$percent}٪ تخفیف اعمال می‌شود.",
        ]);
    }
}
