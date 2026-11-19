<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

final readonly class InvoiceContactData
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
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
