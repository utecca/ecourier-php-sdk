<?php

declare(strict_types=1);

namespace Ecourier\Data;

class CompanyAuthorisationSignerData
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $title,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            title: $data['title'],
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'title' => $this->title,
        ];
    }
}
