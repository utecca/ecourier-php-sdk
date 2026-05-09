<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDocumentContentRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/documents/{$this->document}/xml";
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/xml',
        ];
    }
}
