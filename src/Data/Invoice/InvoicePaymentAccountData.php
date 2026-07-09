<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

use Ecourier\Enums\AccountSchemeId;

class InvoicePaymentAccountData
{
    public function __construct(
        public readonly string $id,
        public readonly AccountSchemeId $scheme,
        public readonly ?string $bankId = null,
        public readonly ?string $bankName = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'scheme' => $this->scheme->value,
        ];

        if ($this->bankId !== null) {
            $data['bank_id'] = $this->bankId;
        }

        if ($this->bankName !== null) {
            $data['bank_name'] = $this->bankName;
        }

        return $data;
    }
}
