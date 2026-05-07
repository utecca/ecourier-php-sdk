<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Resources;

use Ecourier\Sdk\Data\ParticipantData;
use Ecourier\Sdk\Requests\Participants\GetParticipantRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class ParticipantsResource extends BaseResource
{
    public function get(string $participant): Response
    {
        return $this->connector->send(new GetParticipantRequest($participant));
    }

    public function find(string $participant): ParticipantData
    {
        return $this->get($participant)->dto();
    }
}
