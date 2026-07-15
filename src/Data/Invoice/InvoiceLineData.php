<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoiceLineData
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $quantity = null,
        public readonly ?string $unitCode = null,
        public readonly ?string $unitPrice = null,
        public readonly ?string $lineTotal = null,
        public readonly ?InvoiceTaxCategoryData $taxCategory = null,
        public readonly ?string $itemId = null,
        public readonly ?string $sellersItemId = null,
        public readonly ?string $buyersItemId = null,
    ) {}

    public function toArray(): array
    {
        $data = ['id' => $this->id];

        foreach ([
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_code' => $this->unitCode,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
            'item_id' => $this->itemId,
            'sellers_item_id' => $this->sellersItemId,
            'buyers_item_id' => $this->buyersItemId,
        ] as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        if ($this->taxCategory !== null) {
            $data['tax_category'] = $this->taxCategory->toArray();
        }

        return $data;
    }
}
