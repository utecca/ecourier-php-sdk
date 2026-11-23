<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Ecourier\Data\Responses\ParticipantData;
use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetParticipantRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $participant,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/participants/' . self::pathParameter($this->participant, 'participant ID');
    }

    public function createDtoFromResponse(Response $response): ParticipantData
    {
        return ParticipantData::fromArray($response->array());
    }
}
