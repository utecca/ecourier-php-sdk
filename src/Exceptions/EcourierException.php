<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use Exception;
use JsonException;
use Saloon\Http\Response;

/**
 * @phpstan-consistent-constructor
 */
class EcourierException extends Exception
{
    public function __construct(
        string $message,
        private readonly Response $response,
        int $code = 0,
    ) {
        parent::__construct($message, $code);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * The API's machine-readable error code, e.g. `Billing.InsufficientBalance`, when the error has one.
     */
    public function getErrorCode(): ?string
    {
        $code = static::decodeBody($this->response)['code'] ?? null;

        return is_string($code) ? $code : null;
    }

    public static function fromResponse(Response $response): static
    {
        $body = static::decodeBody($response);
        $message = $body['message'] ?? $body['error'] ?? "Request failed with status {$response->status()}.";

        return new static($message, $response, $response->status());
    }

    /**
     * Error bodies are not always JSON, e.g. when a proxy in front of the API returns an HTML 502 page.
     */
    protected static function decodeBody(Response $response): array
    {
        try {
            return $response->json();
        } catch (JsonException) {
            return [];
        }
    }
}
