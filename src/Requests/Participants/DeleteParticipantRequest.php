<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteParticipantRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $participant,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/participants/' . self::pathParameter($this->participant, 'participant ID');
    }
}
