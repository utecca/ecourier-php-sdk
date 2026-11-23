<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use Exception;
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
        $message = static::firstString($body, 'message', 'error') ?? "Request failed with status {$response->status()}.";

        return new static($message, $response, $response->status());
    }

    /**
     * Error bodies are not always a JSON object, e.g. when a proxy in front of the API returns
     * an HTML 502 page or a bare JSON string. Those count as an empty body.
     */
    protected static function decodeBody(Response $response): array
    {
        $body = json_decode($response->body(), true);

        return is_array($body) ? $body : [];
    }

    /**
     * The first of the given fields that holds a string.
     */
    protected static function firstString(array $body, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            if (is_string($body[$key] ?? null)) {
                return $body[$key];
            }
        }

        return null;
    }
}
