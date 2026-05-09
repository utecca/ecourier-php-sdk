<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Invoice;

use Ecourier\Sdk\Enums\PaymentMeansCode;

class InvoicePaymentMeansData
{
    public function __construct(
        public readonly PaymentMeansCode $code,
        public readonly ?int $id = null,
        public readonly ?InvoicePaymentAccountData $account = null,
    ) {}

    public function toArray(): array
    {
        $data = ['code' => $this->code->value];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }

        if ($this->account !== null) {
            $data['account'] = $this->account->toArray();
        }

        return $data;
    }
}
