<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Resources;

use Ecourier\Sdk\Data\DocumentData;
use Ecourier\Sdk\Data\Invoice\InvoiceDocumentData;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\DocumentStatus;
use Ecourier\Sdk\Enums\IdentifierScheme;
use Ecourier\Sdk\Pagination\DocumentsPaginator;
use Ecourier\Sdk\Requests\Documents\GetDocumentsRequest;
use Ecourier\Sdk\Requests\Documents\GetDocumentRequest;
use Ecourier\Sdk\Requests\Documents\GetDocumentContentRequest;
use Ecourier\Sdk\Requests\Documents\GetDocumentHtmlRequest;
use Ecourier\Sdk\Requests\Documents\GetDocumentPdfRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsJsonRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsXmlRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class DocumentsResource extends BaseResource
{
    public function list(
        ?DocumentStatus $status = null,
        ?string $createdAt = null,
        ?string $identityId = null,
        ?string $sort = null,
        int $perPage = 10,
    ): DocumentsPaginator {
        $request = new GetDocumentsRequest(
            status: $status,
            createdAt: $createdAt,
            identityId: $identityId,
            sort: $sort,
            perPage: $perPage,
        );

        return new DocumentsPaginator($this->connector, $request);
    }

    public function get(string $document): Response
    {
        return $this->connector->send(new GetDocumentRequest($document));
    }

    public function find(string $document): DocumentData
    {
        return $this->get($document)->dto();
    }

    public function sendJson(Channel $channel, InvoiceDocumentData $document): Response
    {
        return $this->connector->send(new SendDocumentAsJsonRequest($channel, $document));
    }

    public function sendXml(
        string $xml,
        Channel $channel,
        IdentifierScheme $senderScheme,
        string $senderId,
        IdentifierScheme $recipientScheme,
        string $recipientId,
    ): Response {
        return $this->connector->send(new SendDocumentAsXmlRequest(
            xml: $xml,
            channel: $channel,
            senderScheme: $senderScheme,
            senderId: $senderId,
            recipientScheme: $recipientScheme,
            recipientId: $recipientId,
        ));
    }

    public function contentAsXml(string $document): Response
    {
        return $this->connector->send(new GetDocumentContentRequest($document));
    }

    public function renderAsHtml(string $document): Response
    {
        return $this->connector->send(new GetDocumentHtmlRequest($document));
    }

    public function renderAsPdf(string $document): Response
    {
        return $this->connector->send(new GetDocumentPdfRequest($document));
    }
}
