<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Ecourier\Data\CreateParticipantData;
use Ecourier\Data\ParticipantData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateParticipantRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly CreateParticipantData $participant,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/participants';
    }

    protected function defaultBody(): array
    {
        return $this->participant->toArray();
    }

    public function createDtoFromResponse(Response $response): ParticipantData
    {
        return ParticipantData::fromArray($response->array());
    }
}
