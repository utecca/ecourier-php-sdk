<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;

readonly class ParticipantData
{
    /** @param Channel[] $channels */
    public function __construct(
        public string $id,
        public ParticipantCompanyData $company,
        public ?ParticipantCapabilitySetData $capabilitySet,
        public Mode $mode,
        public IdentifierScheme $scheme,
        public string $identifier,
        public string $schemeIcd,
        public string $fullIdentifier,
        public array $channels,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            company: ParticipantCompanyData::fromArray($data['company']),
            capabilitySet: isset($data['capability_set']) ? ParticipantCapabilitySetData::fromArray($data['capability_set']) : null,
            mode: Mode::from($data['mode']),
            scheme: IdentifierScheme::from($data['scheme']),
            identifier: $data['identifier'],
            schemeIcd: $data['scheme_icd'],
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
            'capability_set' => $this->capabilitySet?->toArray(),
            'mode' => $this->mode->value,
            'scheme' => $this->scheme->value,
            'identifier' => $this->identifier,
            'scheme_icd' => $this->schemeIcd,
            'full_identifier' => $this->fullIdentifier,
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:sP'),
            'updated_at' => $this->updatedAt->format('Y-m-d\TH:i:sP'),
        ];
    }
}
