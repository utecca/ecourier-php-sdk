<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use Ecourier\Enums\Locale;
use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDocumentPdfRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
        private readonly ?Locale $locale = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents/' . self::pathParameter($this->document, 'document ID') . '/pdf';
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
