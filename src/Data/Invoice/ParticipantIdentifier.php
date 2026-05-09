<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Invoice;

use Ecourier\Sdk\Enums\IdentifierScheme;

class ParticipantIdentifier
{
    public function __construct(
        public readonly IdentifierScheme $scheme,
        public readonly string $id,
    ) {}

    public function toArray(): array
    {
        return [
            'scheme' => $this->scheme->value,
            'id' => $this->id,
        ];
    }
}
