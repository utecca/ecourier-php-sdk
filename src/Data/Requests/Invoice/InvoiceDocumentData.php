<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

use Ecourier\Enums\Currency;
use Ecourier\Enums\InvoiceDocumentType;

readonly class InvoiceDocumentData
{
    /**
     * @param InvoiceLineData[] $lines
     * @param InvoiceDocumentReferenceData[]|null $invoiceDocumentReferences Earlier invoices this document refers to. On a credit note: the invoices it credits.
     */
    public function __construct(
        public InvoiceDocumentType $type,
        public string $id,
        public string $issueDate,
        public Currency $currency,
        public InvoicePartyData $supplier,
        public InvoicePartyData $customer,
        public array $lines,
        public InvoiceTotalsData $totals,
        public ?string $uuid = null,
        public ?string $dueDate = null,
        public ?string $orderReference = null,
        public ?InvoicePaymentData $payment = null,
        public ?array $invoiceDocumentReferences = null,
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

        if ($this->invoiceDocumentReferences !== null) {
            $data['invoice_document_references'] = array_map(
                fn(InvoiceDocumentReferenceData $reference) => $reference->toArray(),
                $this->invoiceDocumentReferences,
            );
        }

        if ($this->payment !== null) {
            $data['payment'] = $this->payment->toArray();
        }

        return $data;
    }
}
