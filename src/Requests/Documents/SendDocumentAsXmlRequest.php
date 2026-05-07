<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Ecourier\Sdk\Data\DocumentData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasXmlBody;

class SendDocumentAsXmlRequest extends Request implements HasBody
{
    use HasXmlBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $xml,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents/xml';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/xml',
        ];
    }

    protected function defaultBody(): string
    {
        return $this->xml;
    }

    public function createDtoFromResponse(Response $response): DocumentData
    {
        return DocumentData::fromArray($response->array());
    }
}
