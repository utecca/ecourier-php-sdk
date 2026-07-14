<?php

declare(strict_types=1);

namespace Ecourier\Requests\Lookup;

use Ecourier\Data\ParticipantLookupData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
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
        return "/lookup/participants/{$this->channel->value}/{$this->scheme->value}/{$this->participantId}";
    }

    public function createDtoFromResponse(Response $response): ParticipantLookupData
    {
        return ParticipantLookupData::fromArray($response->array());
    }
}
