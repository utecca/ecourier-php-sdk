<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Enums\IdentifierScheme;

readonly class DocumentParticipantData
{
    public function __construct(
        public IdentifierScheme $scheme,
        public string $identifier,
        public string $schemeIcd,
        public string $fullIdentifier,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            scheme: IdentifierScheme::from($data['scheme']),
            identifier: $data['identifier'],
            schemeIcd: $data['scheme_icd'],
            fullIdentifier: $data['full_identifier'],
        );
    }

    public function toArray(): array
    {
        return [
            'scheme' => $this->scheme->value,
            'identifier' => $this->identifier,
            'scheme_icd' => $this->schemeIcd,
            'full_identifier' => $this->fullIdentifier,
        ];
    }
}
