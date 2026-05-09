<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Participants;

use Ecourier\Sdk\Data\ParticipantData;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\IdentifierScheme;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetParticipantRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly Channel $channel,
        private readonly IdentifierScheme $scheme,
        private readonly string $participantId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/lookup/{$this->channel->value}/{$this->scheme->value}/{$this->participantId}";
    }

    public function createDtoFromResponse(Response $response): ParticipantData
    {
        return ParticipantData::fromArray($response->array());
    }
}
