<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

use Ecourier\Enums\IdentifierScheme;

class ParticipantData
{
    public function __construct(
        public readonly string $participantId,
        public readonly string $participantIdentifier,
        public readonly string $participantIdentifierIcd,
        public readonly IdentifierScheme $participantIdentifierScheme,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            participantId: $data['participant_id'],
            participantIdentifier: $data['participant_identifier'],
            participantIdentifierIcd: $data['participant_identifier_icd'],
            participantIdentifierScheme: IdentifierScheme::from($data['participant_identifier_scheme']),
        );
    }

    public function toArray(): array
    {
        return [
            'participant_id' => $this->participantId,
            'participant_identifier' => $this->participantIdentifier,
            'participant_identifier_icd' => $this->participantIdentifierIcd,
            'participant_identifier_scheme' => $this->participantIdentifierScheme->value,
        ];
    }
}
