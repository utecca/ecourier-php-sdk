<?php

declare(strict_types=1);

namespace Ecourier\Data;

use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

class CreateParticipantData
{
    /** @param Channel[] $channels */
    public function __construct(
        public readonly string $companyId,
        public readonly IdentifierScheme $scheme,
        public readonly array $channels,
        public readonly ?string $identifier = null,
    ) {}

    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'scheme' => $this->scheme->value,
            'identifier' => $this->identifier,
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
        ];
    }
}
