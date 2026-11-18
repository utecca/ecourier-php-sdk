<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

readonly class InvoiceLineData
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?string $quantity = null,
        public ?string $unitCode = null,
        public ?string $unitPrice = null,
        public ?string $lineTotal = null,
        public ?InvoiceTaxCategoryData $taxCategory = null,
        public ?string $itemId = null,
        public ?string $sellersItemId = null,
        public ?string $buyersItemId = null,
    ) {}

    public function toArray(): array
    {
        $data = ['id' => (string) $this->id, 'name' => $this->name];

        foreach ([
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
