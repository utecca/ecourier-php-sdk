<?php

declare(strict_types=1);

namespace Ecourier\Exceptions;

use Saloon\Http\Response;

class ValidationException extends EcourierException
{
    private readonly array $errors;

    public function __construct(string $message, Response $response, int $code = 422, array $errors = [])
    {
        parent::__construct($message, $response, $code);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function fromResponse(Response $response): static
    {
        $body = static::decodeBody($response);
        $message = $body['message'] ?? 'Validation failed.';
        $errors = $body['errors'] ?? [];

        return new static($message, $response, $response->status(), $errors);
    }
}
