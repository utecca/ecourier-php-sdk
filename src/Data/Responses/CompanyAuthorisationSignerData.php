<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class CompanyAuthorisationSignerData
{
    use ParsesResponseData;

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $title,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: self::required($data, 'first_name'),
            lastName: self::required($data, 'last_name'),
            title: self::required($data, 'title'),
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
