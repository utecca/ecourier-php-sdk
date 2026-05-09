<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Invoice;

use Ecourier\Sdk\Enums\TaxCategoryCode;

class InvoiceTaxCategoryData
{
    public function __construct(
        public readonly TaxCategoryCode $code,
    ) {}

    public function toArray(): array
    {
        return ['code' => $this->code->value];
    }
}
