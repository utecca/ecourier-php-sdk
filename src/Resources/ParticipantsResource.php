<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\ParticipantData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Requests\Participants\GetParticipantRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class ParticipantsResource extends BaseResource
{
    public function get(Channel $channel, IdentifierScheme $scheme, string $participantId): Response
    {
        return $this->connector->send(new GetParticipantRequest($channel, $scheme, $participantId));
    }

    public function find(Channel $channel, IdentifierScheme $scheme, string $participantId): ParticipantData
    {
        return $this->get($channel, $scheme, $participantId)->dto();
    }
}
