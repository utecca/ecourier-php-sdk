<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Ecourier\Data\Requests\UpdateParticipantData;
use Ecourier\Data\Responses\ParticipantData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Undefined;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateParticipantRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        private readonly string $participant,
        private readonly UpdateParticipantData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/participants/{$this->participant}";
    }

    protected function defaultBody(): array
    {
        $body = [];

        if ($this->data->channels !== null) {
            $body['channels'] = array_map(fn(Channel $channel) => $channel->value, $this->data->channels);
        }

        if (!($this->data->capabilitySetId instanceof Undefined)) {
            $body['capability_set_id'] = $this->data->capabilitySetId;
        }

        return $body;
    }

    public function createDtoFromResponse(Response $response): ParticipantData
    {
        return ParticipantData::fromArray($response->array());
    }
}
