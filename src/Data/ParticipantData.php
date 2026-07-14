<?php

declare(strict_types=1);

namespace Ecourier\Data;

use DateTimeImmutable;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;

class ParticipantData
{
    /** @param Channel[] $channels */
    public function __construct(
        public readonly string $id,
        public readonly ParticipantCompanyData $company,
        public readonly Mode $mode,
        public readonly IdentifierScheme $scheme,
        public readonly string $identifier,
        public readonly string $fullIdentifier,
        public readonly array $channels,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            company: ParticipantCompanyData::fromArray($data['company']),
            mode: Mode::from($data['mode']),
            scheme: IdentifierScheme::from($data['scheme']),
            identifier: $data['identifier'],
            fullIdentifier: $data['full_identifier'],
            channels: array_map(fn(string $channel) => Channel::from($channel), $data['channels']),
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTimeImmutable($data['updated_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company' => $this->company->toArray(),
            'mode' => $this->mode->value,
            'scheme' => $this->scheme->value,
            'identifier' => $this->identifier,
            'full_identifier' => $this->fullIdentifier,
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:sP'),
            'updated_at' => $this->updatedAt->format('Y-m-d\TH:i:sP'),
        ];
    }
}
