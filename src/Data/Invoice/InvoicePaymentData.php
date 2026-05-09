<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Invoice;

class InvoicePaymentData
{
    /** @param InvoicePaymentMeansData[]|null $paymentMeans */
    public function __construct(
        public readonly ?array $paymentMeans = null,
    ) {}

    public function toArray(): array
    {
        $data = [];

        if ($this->paymentMeans !== null) {
            $data['payment_means'] = array_map(fn($m) => $m->toArray(), $this->paymentMeans);
        }

        return $data;
    }
}
