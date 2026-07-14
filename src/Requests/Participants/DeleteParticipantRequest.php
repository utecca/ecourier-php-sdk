<?php

declare(strict_types=1);

namespace Ecourier\Requests\Participants;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteParticipantRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $participant,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/participants/{$this->participant}";
    }
}
