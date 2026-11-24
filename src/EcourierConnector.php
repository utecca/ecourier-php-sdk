<?php

declare(strict_types=1);

namespace Ecourier;

use Composer\InstalledVersions;
use Ecourier\Exceptions\AuthenticationException;
use Ecourier\Exceptions\BadRequestException;
use Ecourier\Exceptions\EcourierException;
use Ecourier\Exceptions\FailedDependencyException;
use Ecourier\Exceptions\ForbiddenException;
use Ecourier\Exceptions\GoneException;
use Ecourier\Exceptions\NotFoundException;
use Ecourier\Exceptions\PaymentRequiredException;
use Ecourier\Exceptions\ValidationException;
use Ecourier\Resources\CompaniesResource;
use Ecourier\Resources\DocumentsResource;
use Ecourier\Resources\LookupResource;
use Ecourier\Resources\ParticipantsResource;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasTimeout;
use SensitiveParameter;
use SensitiveParameterValue;

class EcourierConnector extends Connector
{
    use AcceptsJson;
    use HasTimeout;

    protected int $connectTimeout = 30;
    protected int $requestTimeout = 60;

    protected ?string $response = EcourierResponse::class;

    private readonly SensitiveParameterValue $apiKey;

    public function __construct(#[SensitiveParameter] string $apiKey)
    {
        $this->apiKey = new SensitiveParameterValue($apiKey);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.ecourier.io/v1';
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator($this->apiKey->getValue());
    }

    protected function defaultHeaders(): array
    {
        return [
            'User-Agent' => $this->userAgent(),
        ];
    }

    private function userAgent(): string
    {
        $version = InstalledVersions::isInstalled('ecourier/ecourier')
            ? InstalledVersions::getPrettyVersion('ecourier/ecourier') ?? 'unknown'
            : 'unknown';

        $saloonVersion = InstalledVersions::isInstalled('saloonphp/saloon')
            ? InstalledVersions::getPrettyVersion('saloonphp/saloon') ?? 'unknown'
            : 'unknown';

        $userAgent = sprintf('ecourier-php-sdk/%s (PHP %s; %s) saloon/%s', $version, PHP_VERSION, PHP_OS_FAMILY, $saloonVersion);

        if (InstalledVersions::isInstalled('ecourier/ecourier-laravel')) {
            $laravelVersion = InstalledVersions::getPrettyVersion('ecourier/ecourier-laravel') ?? 'unknown';
            $userAgent .= sprintf(' ecourier-laravel/%s', $laravelVersion);
        }

        return $userAgent;
    }

    public function boot(\Saloon\Http\PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onResponse(function (Response $response): void {
            if ($response->successful()) {
                return;
            }

            throw match ($response->status()) {
                400 => BadRequestException::fromResponse($response),
                401 => AuthenticationException::fromResponse($response),
                402 => PaymentRequiredException::fromResponse($response),
                403 => ForbiddenException::fromResponse($response),
                404 => NotFoundException::fromResponse($response),
                410 => GoneException::fromResponse($response),
                422 => ValidationException::fromResponse($response),
                424 => FailedDependencyException::fromResponse($response),
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

    public function lookup(): LookupResource
    {
        return new LookupResource($this);
    }

    public function participants(): ParticipantsResource
    {
        return new ParticipantsResource($this);
    }
}
