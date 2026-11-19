<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests\Invoice;

use Ecourier\Enums\IdentifierScheme;

final readonly class ParticipantIdentifier
{
    public function __construct(
        public IdentifierScheme $scheme,
        public string $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            scheme: IdentifierScheme::from($data['scheme']),
            id: $data['id'],
        );
    }

    public function toArray(): array
    {
        return [
            'scheme' => $this->scheme->value,
            'id' => $this->id,
        ];
    }
}
