<?php

declare(strict_types=1);

namespace Ecourier\Requests\Lookup;

use Ecourier\Data\Responses\ParticipantLookupData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetParticipantRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly Channel $channel,
        private readonly IdentifierScheme $scheme,
        private readonly string $participantId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/lookup/participants/{$this->channel->value}/{$this->scheme->value}/" . self::pathParameter($this->participantId, 'participant ID');
    }

    public function createDtoFromResponse(Response $response): ParticipantLookupData
    {
        return ParticipantLookupData::fromArray($response->array());
    }
}
