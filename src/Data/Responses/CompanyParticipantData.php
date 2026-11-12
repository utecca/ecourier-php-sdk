<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

readonly class CompanyParticipantData
{
    /** @param Channel[] $channels */
    public function __construct(
        public string $id,
        public IdentifierScheme $scheme,
        public string $identifier,
        public string $schemeIcd,
        public string $fullIdentifier,
        public array $channels,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            scheme: IdentifierScheme::from($data['scheme']),
            identifier: $data['identifier'],
            schemeIcd: $data['scheme_icd'],
            fullIdentifier: $data['full_identifier'],
            channels: array_map(fn(string $channel) => Channel::from($channel), $data['channels']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'scheme' => $this->scheme->value,
            'identifier' => $this->identifier,
            'scheme_icd' => $this->schemeIcd,
            'full_identifier' => $this->fullIdentifier,
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
        ];
    }
}
