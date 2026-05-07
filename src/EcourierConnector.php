<?php

declare(strict_types=1);

namespace Ecourier\Sdk;

use Ecourier\Sdk\Exceptions\AuthenticationException;
use Ecourier\Sdk\Exceptions\EcourierException;
use Ecourier\Sdk\Exceptions\NotFoundException;
use Ecourier\Sdk\Exceptions\ValidationException;
use Ecourier\Sdk\Resources\CompaniesResource;
use Ecourier\Sdk\Resources\DocumentsResource;
use Ecourier\Sdk\Resources\ParticipantsResource;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\PaginationPlugin\Paginator;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;

class EcourierConnector extends Connector implements HasPagination
{
    use AcceptsJson;
    use HasTimeout;

    protected int $connectTimeout = 30;
    protected int $requestTimeout = 60;

    public function __construct(
        private readonly string $apiKey,
        private readonly bool $sandbox = false,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.api.ecourier.io/v1'
            : 'https://api.ecourier.io/v1';
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

    public function paginate(\Saloon\Http\Request $request): Paginator
    {
        return new \Ecourier\Sdk\Pagination\DocumentsPaginator($this, $request);
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
