<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

final readonly class InvoiceDocumentReferenceData
{
    /**
     * @param string $id Number of the earlier invoice, at most 100 characters.
     * @param ?string $issueDate Issue date of the earlier invoice (Y-m-d).
     */
    public function __construct(
        public string $id,
        public ?string $issueDate = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'issue_date' => $this->issueDate,
        ], fn($value) => $value !== null);
    }
}
