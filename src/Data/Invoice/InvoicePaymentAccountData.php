<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

use Ecourier\Enums\AccountSchemeId;

class InvoicePaymentAccountData
{
    public function __construct(
        public readonly string $id,
        public readonly AccountSchemeId $scheme,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'scheme' => $this->scheme->value,
        ];
    }
}
