<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

use Ecourier\Enums\Currency;
use Ecourier\Enums\DocumentType;

class InvoiceDocumentData
{
    /** @param InvoiceLineData[] $lines */
    public function __construct(
        public readonly DocumentType $type,
        public readonly string $id,
        public readonly string $issueDate,
        public readonly Currency $currency,
        public readonly InvoicePartyData $supplier,
        public readonly InvoicePartyData $customer,
        public readonly array $lines,
        public readonly InvoiceTotalsData $totals,
        public readonly ?string $uuid = null,
        public readonly ?string $dueDate = null,
        public readonly ?string $orderReference = null,
        public readonly ?InvoicePaymentData $payment = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'type' => $this->type->value,
            'id' => $this->id,
            'issue_date' => $this->issueDate,
            'currency' => $this->currency->value,
            'supplier' => $this->supplier->toArray(),
            'customer' => $this->customer->toArray(),
            'lines' => array_map(fn($l) => $l->toArray(), $this->lines),
            'totals' => $this->totals->toArray(),
        ];

        if ($this->uuid !== null) {
            $data['uuid'] = $this->uuid;
        }

        if ($this->dueDate !== null) {
            $data['due_date'] = $this->dueDate;
        }

        if ($this->orderReference !== null) {
            $data['order_reference'] = $this->orderReference;
        }

        if ($this->payment !== null) {
            $data['payment'] = $this->payment->toArray();
        }

        return $data;
    }
}
