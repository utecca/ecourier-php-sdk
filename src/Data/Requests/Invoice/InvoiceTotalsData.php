<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

readonly class InvoiceTotalsData
{
    public function __construct(
        public string $subtotalAmount,
        public string $taxAmount,
        public string $totalAmount,
    ) {}

    public function toArray(): array
    {
        return [
            'subtotal_amount' => $this->subtotalAmount,
            'tax_amount' => $this->taxAmount,
            'total_amount' => $this->totalAmount,
        ];
    }
}
