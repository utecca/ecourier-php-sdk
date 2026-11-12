<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

readonly class CompanyAuthorisationSignerData
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $title,
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
