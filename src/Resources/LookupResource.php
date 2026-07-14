<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\ParticipantLookupData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Requests\Lookup\GetParticipantRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class LookupResource extends BaseResource
{
    public function get(Channel $channel, IdentifierScheme $scheme, string $participantId): Response
    {
        return $this->connector->send(new GetParticipantRequest($channel, $scheme, $participantId));
    }

    public function findParticipant(Channel $channel, IdentifierScheme $scheme, string $participantId): ParticipantLookupData
    {
        return $this->get($channel, $scheme, $participantId)->dto();
    }
}
