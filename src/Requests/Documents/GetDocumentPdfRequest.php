<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use Ecourier\Enums\Locale;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDocumentPdfRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
        private readonly ?Locale $locale = null,
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

    protected function defaultQuery(): array
    {
        return array_filter([
            'locale' => $this->locale?->value,
        ]);
    }
}
