<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Ecourier\Data\ParticipantData;
use Ecourier\Enums\Channel;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateParticipantRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    /** @param Channel[] $channels */
    public function __construct(
        private readonly string $participant,
        private readonly array $channels,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/participants/{$this->participant}";
    }

    protected function defaultBody(): array
    {
        return [
            'channels' => array_map(fn(Channel $channel) => $channel->value, $this->channels),
        ];
    }

    public function createDtoFromResponse(Response $response): ParticipantData
    {
        return ParticipantData::fromArray($response->array());
    }
}
