<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoiceTotalsData
{
    public function __construct(
        public readonly string $subtotalAmount,
        public readonly string $taxAmount,
        public readonly string $totalAmount,
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
