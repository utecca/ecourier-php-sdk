<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

readonly class SimplifiedAddressData
{
    public function __construct(
        public string $streetName,
        public string $city,
        public string $postalCode,
        public string $country,
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
