<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

use Ecourier\Enums\TaxCategoryCode;

final readonly class InvoiceTaxCategoryData
{
    public function __construct(
        public TaxCategoryCode $code,
        public ?string $percent = null,
        public ?string $exemptionReason = null,
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
