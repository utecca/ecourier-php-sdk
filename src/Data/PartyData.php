<?php

declare(strict_types=1);

namespace Ecourier\Data;

class PartyData
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $name = null,
        public readonly ?string $cvr = null,
        public readonly ?string $vat = null,
        public readonly ?string $country = null,
        public readonly ?string $email = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            cvr: $data['cvr'] ?? null,
            vat: $data['vat'] ?? null,
            country: $data['country'] ?? null,
            email: $data['email'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cvr' => $this->cvr,
            'vat' => $this->vat,
            'country' => $this->country,
            'email' => $this->email,
        ];
    }
}
