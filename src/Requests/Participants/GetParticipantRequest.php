<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Ecourier\Data\ParticipantData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetParticipantRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $participant,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/participants/{$this->participant}";
    }

    public function createDtoFromResponse(Response $response): ParticipantData
    {
        return ParticipantData::fromArray($response->array());
    }
}
