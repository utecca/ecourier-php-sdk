<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use Ecourier\Data\DocumentData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class MarkDocumentDeliveredRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        private readonly string $document,
        private readonly bool $delivered = true,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/documents/{$this->document}/delivered";
    }

    protected function defaultBody(): array
    {
        return ['delivered' => $this->delivered];
    }

    public function createDtoFromResponse(Response $response): DocumentData
    {
        return DocumentData::fromArray($response->array());
    }
}
