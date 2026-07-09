<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

use Ecourier\Enums\PaymentMeansCode;

class InvoicePaymentMeansData
{
    public function __construct(
        public readonly PaymentMeansCode $code,
        public readonly ?int $id = null,
        public readonly ?string $remittanceText = null,
        public readonly ?string $instruction = null,
        public readonly ?InvoicePaymentAccountData $account = null,
    ) {}

    public function toArray(): array
    {
        $data = ['code' => $this->code->value];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->remittanceText !== null) {
            $data['remittance_text'] = $this->remittanceText;
        }

        if ($this->instruction !== null) {
            $data['instruction'] = $this->instruction;
        }

        if ($this->account !== null) {
            $data['account'] = $this->account->toArray();
        }

        return $data;
    }
}
