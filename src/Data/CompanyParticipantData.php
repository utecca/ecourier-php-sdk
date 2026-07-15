<?php

declare(strict_types=1);

namespace Ecourier\Data;

use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

class CompanyParticipantData
{
    /** @param Channel[] $channels */
    public function __construct(
        public readonly string $id,
        public readonly string $fullIdentifier,
        public readonly IdentifierScheme $scheme,
        public readonly string $schemeIcd,
        public readonly string $identifier,
        public readonly array $channels,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            fullIdentifier: $data['full_identifier'],
            scheme: IdentifierScheme::from($data['scheme']),
            schemeIcd: $data['scheme_icd'],
            identifier: $data['identifier'],
            channels: array_map(fn(string $channel) => Channel::from($channel), $data['channels']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_identifier' => $this->fullIdentifier,
            'scheme' => $this->scheme->value,
            'scheme_icd' => $this->schemeIcd,
            'identifier' => $this->identifier,
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
        ];
    }
}
