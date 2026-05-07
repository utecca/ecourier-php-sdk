<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Ecourier\Sdk\Data\DocumentData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class SendDocumentAsJsonRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly array $payload,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents/json';
    }

    protected function defaultBody(): array
    {
        return $this->payload;
    }

    public function createDtoFromResponse(Response $response): DocumentData
    {
        return DocumentData::fromArray($response->array());
    }
}
