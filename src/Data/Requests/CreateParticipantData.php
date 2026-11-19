<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests;

use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

final readonly class CreateParticipantData
{
    /** @param Channel[] $channels */
    public function __construct(
        public string $companyId,
        public array $channels,
        public IdentifierScheme $scheme,
        public ?string $identifier = null,
        public ?string $capabilitySetId = null,
    ) {}

    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
            'scheme' => $this->scheme->value,
            'identifier' => $this->identifier,
            'capability_set_id' => $this->capabilitySetId,
        ];
    }
}
