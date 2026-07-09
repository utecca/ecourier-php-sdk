<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoicePaymentData
{
    /** @param InvoicePaymentMeansData[]|null $paymentMeans */
    public function __construct(
        public readonly ?array $paymentMeans = null,
        public readonly ?string $paymentTermsNote = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->paymentMeans !== null) {
            $data['payment_means'] = array_map(fn($m) => $m->toArray(), $this->paymentMeans);
        }

        if ($this->paymentTermsNote !== null) {
            $data['payment_terms_note'] = $this->paymentTermsNote;
        }

        return $data;
    }
}
