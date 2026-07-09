<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoiceLineData
{
    public function __construct(
        public readonly int $id,
        public readonly ?InvoiceTaxCategoryData $taxCategory = null,
    ) {}

    public function toArray(): array
    {
        $data = ['id' => $this->id];

        if ($this->taxCategory !== null) {
            $data['tax_category'] = $this->taxCategory->toArray();
        }

        return $data;
    }
}
