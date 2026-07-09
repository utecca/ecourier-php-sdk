<?php

declare(strict_types=1);

namespace Ecourier\Data;

class CompanyListItemData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $country,
        public readonly string $companyNo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            country: $data['country'],
            companyNo: $data['company_no'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'country' => $this->country,
            'company_no' => $this->companyNo,
        ];
    }
}
