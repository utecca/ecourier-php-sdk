<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDocumentContentRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents/' . self::pathParameter($this->document, 'document ID') . '/xml';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/xml',
        ];
    }
}
