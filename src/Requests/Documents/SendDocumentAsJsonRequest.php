<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Ecourier\Sdk\Data\DocumentData;
use Ecourier\Sdk\Data\Invoice\InvoiceDocumentData;
use Ecourier\Sdk\Enums\Channel;
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
        private readonly Channel $channel,
        private readonly InvoiceDocumentData $document,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents/json';
    }

    protected function defaultBody(): array
    {
        return [
            'channel' => $this->channel->value,
            'document' => $this->document->toArray(),
        ];
    }

    public function createDtoFromResponse(Response $response): DocumentData
    {
        return DocumentData::fromArray($response->array());
    }
}
