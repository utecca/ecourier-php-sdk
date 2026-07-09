<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

use Ecourier\Enums\TaxCategoryCode;

class InvoiceTaxCategoryData
{
    public function __construct(
        public readonly TaxCategoryCode $code,
        public readonly ?string $percent = null,
        public readonly ?string $exemptionReason = null,
    ) {}

    public function toArray(): array
    {
        $data = ['code' => $this->code->value];

        if ($this->percent !== null) {
            $data['percent'] = $this->percent;
        }

        if ($this->exemptionReason !== null) {
            $data['exemption_reason'] = $this->exemptionReason;
        }

        return $data;
    }
}
