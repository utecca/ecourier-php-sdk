<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

class CompanyData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $cvr = null,
        public readonly ?string $vat = null,
        public readonly ?string $country = null,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?AddressData $address = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            cvr: $data['cvr'] ?? null,
            vat: $data['vat'] ?? null,
            country: $data['country'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: isset($data['address']) ? AddressData::fromArray($data['address']) : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
