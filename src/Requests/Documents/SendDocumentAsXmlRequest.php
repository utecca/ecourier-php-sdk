<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Ecourier\Sdk\Data\DocumentData;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\IdentifierScheme;
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
        private readonly Channel $channel,
        private readonly IdentifierScheme $senderScheme,
        private readonly string $senderId,
        private readonly IdentifierScheme $recipientScheme,
        private readonly string $recipientId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents/xml';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/xml',
            'Ecourier-Channel' => $this->channel->value,
            'Ecourier-Sender-Scheme' => $this->senderScheme->value,
            'Ecourier-Sender-Id' => $this->senderId,
            'Ecourier-Recipient-Scheme' => $this->recipientScheme->value,
            'Ecourier-Recipient-Id' => $this->recipientId,
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
