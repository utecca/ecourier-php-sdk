<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use Ecourier\Data\DocumentData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetDocumentRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $document,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/documents/{$this->document}";
    }

    public function createDtoFromResponse(Response $response): DocumentData
    {
        return DocumentData::fromArray($response->array());
    }
}
