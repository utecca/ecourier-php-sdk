<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDocumentPdfRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/documents/{$this->document}/pdf";
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/pdf',
        ];
    }
}
