<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDocumentHtmlRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/documents/{$this->document}/html";
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'text/html',
        ];
    }
}
