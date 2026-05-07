<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Exceptions;

use Exception;
use Saloon\Http\Response;

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

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();
        $message = $body['message'] ?? $body['error'] ?? 'An unknown error occurred.';

        return new static($message, $response, $response->status());
    }
}
