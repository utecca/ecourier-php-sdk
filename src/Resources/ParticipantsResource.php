<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\CreateParticipantData;
use Ecourier\Data\ParticipantData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Pagination\ParticipantsPaginator;
use Ecourier\Requests\Participants\CreateParticipantRequest;
use Ecourier\Requests\Participants\DeleteParticipantRequest;
use Ecourier\Requests\Participants\GetParticipantRequest;
use Ecourier\Requests\Participants\GetParticipantsRequest;
use Ecourier\Requests\Participants\UpdateParticipantRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class ParticipantsResource extends BaseResource
{
    public function list(
        string|array|null $companyId = null,
        IdentifierScheme|array|null $scheme = null,
        Channel|array|null $channel = null,
        int $perPage = 10,
    ): ParticipantsPaginator {
        return new ParticipantsPaginator($this->connector, new GetParticipantsRequest(
            companyId: $companyId,
            scheme: $scheme,
            channel: $channel,
            perPage: $perPage,
        ));
    }

    public function get(string $participant): Response
    {
        return $this->connector->send(new GetParticipantRequest($participant));
    }

    public function find(string $participant): ParticipantData
    {
        return $this->get($participant)->dto();
    }

    public function create(CreateParticipantData $participant): ParticipantData
    {
        return $this->connector->send(new CreateParticipantRequest($participant))->dto();
    }

    /** @param Channel[] $channels */
    public function update(string $participant, array $channels): ParticipantData
    {
        return $this->connector->send(new UpdateParticipantRequest($participant, $channels))->dto();
    }

    public function delete(string $participant): Response
    {
        return $this->connector->send(new DeleteParticipantRequest($participant));
    }
}
