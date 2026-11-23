<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use Exception;
use JsonException;
use Saloon\Http\Response;
use SensitiveParameterValue;

/**
 * @phpstan-consistent-constructor
 */
class EcourierException extends Exception
{
    /**
     * The response holds the request, and with it the API key. It is wrapped so a dumped exception does not show it.
     */
    private readonly SensitiveParameterValue $response;

    public function __construct(
        string $message,
        Response $response,
        int $code = 0,
    ) {
        parent::__construct($message, $code);

        $this->response = new SensitiveParameterValue($response);
    }

    public function getResponse(): Response
    {
        return $this->response->getValue();
    }

    /**
     * Dumps the exception as usual, but without the arguments of the stack frames: they hold the request as well.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        $properties = (array) $this;
        $properties["\0Exception\0trace"] = array_map(
            fn(array $frame) => array_diff_key($frame, ['args' => true]),
            $this->getTrace(),
        );

        return $properties;
    }

    /**
     * The API's machine-readable error code, e.g. `Billing.InsufficientBalance`, when the error has one.
     */
    public function getErrorCode(): ?string
    {
        $code = static::decodeBody($this->getResponse())['code'] ?? null;

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
