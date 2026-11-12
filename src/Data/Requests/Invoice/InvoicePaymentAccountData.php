<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

use Ecourier\Enums\AccountSchemeId;

readonly class InvoicePaymentAccountData
{
    public function __construct(
        public string $id,
        public AccountSchemeId $scheme,
        public ?string $bankId = null,
        public ?string $bankName = null,
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
