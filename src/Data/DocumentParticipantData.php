<?php

declare(strict_types=1);

namespace Ecourier\Data;

use Ecourier\Enums\IdentifierScheme;

class DocumentParticipantData
{
    public function __construct(
        public readonly string $fullIdentifier,
        public readonly IdentifierScheme $scheme,
        public readonly string $schemeIcd,
        public readonly string $identifier,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            fullIdentifier: $data['full_identifier'],
            scheme: IdentifierScheme::from($data['scheme']),
            schemeIcd: $data['scheme_icd'],
            identifier: $data['identifier'],
        );
    }

    public function toArray(): array
    {
        return [
            'full_identifier' => $this->fullIdentifier,
            'scheme' => $this->scheme->value,
            'scheme_icd' => $this->schemeIcd,
            'identifier' => $this->identifier,
        ];
    }
}
