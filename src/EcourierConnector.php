<?php

declare(strict_types=1);

namespace Ecourier;

use Ecourier\Exceptions\AuthenticationException;
use Ecourier\Exceptions\EcourierException;
use Ecourier\Exceptions\NotFoundException;
use Ecourier\Exceptions\ValidationException;
use Ecourier\Resources\CompaniesResource;
use Ecourier\Resources\DocumentsResource;
use Ecourier\Resources\ParticipantsResource;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class EcourierConnector extends Connector
{
    use AcceptsJson;
    use HasTimeout;

    protected int $connectTimeout = 30;
    protected int $requestTimeout = 60;

    public function __construct(
        private readonly string $apiKey,
    ) {}

    public function resolveBaseUrl(): string
    {
        return 'https://api.ecourier.io/v1';
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->apiKey);
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function boot(\Saloon\Http\PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onResponse(function (Response $response): void {
            if ($response->successful()) {
                return;
            }

            throw match ($response->status()) {
                401 => AuthenticationException::fromResponse($response),
                404 => NotFoundException::fromResponse($response),
                422 => ValidationException::fromResponse($response),
                default => EcourierException::fromResponse($response),
            };
        });
    }

    public function companies(): CompaniesResource
    {
        return new CompaniesResource($this);
    }

    public function documents(): DocumentsResource
    {
        return new DocumentsResource($this);
    }

    public function participants(): ParticipantsResource
    {
        return new ParticipantsResource($this);
    }
}
