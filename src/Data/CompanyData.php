<?php

declare(strict_types=1);

namespace Ecourier\Data;

use DateTimeImmutable;

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
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
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
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
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
            'phone' => $this->phone,
            'address' => $this->address?->toArray(),
            'created_at' => $this->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $this->updatedAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
