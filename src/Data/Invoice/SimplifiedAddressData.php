<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Invoice;

class SimplifiedAddressData
{
    public function __construct(
        public readonly string $streetName,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country,
    ) {}

    public function toArray(): array
    {
        return [
            'street_name' => $this->streetName,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
        ];
    }
}
