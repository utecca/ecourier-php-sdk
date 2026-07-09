<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Webhook;

class ParticipantData
{
    public function __construct(
        public readonly string $name,
        public readonly string $identifierScheme,
        public readonly string $identifierValue,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            identifierScheme: $data['identifier_scheme'],
            identifierValue: $data['identifier_value'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'identifier_scheme' => $this->identifierScheme,
            'identifier_value' => $this->identifierValue,
        ];
    }
}
