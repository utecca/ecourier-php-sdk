<?php

declare(strict_types=1);

namespace Ecourier\Data\Invoice;

class InvoiceContactData
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ], fn($value) => $value !== null);
    }
}
