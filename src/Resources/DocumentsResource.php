<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\DocumentData;
use Ecourier\Data\Invoice\InvoiceDocumentData;
use Ecourier\Data\SendDocumentData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Sort;
use Ecourier\Pagination\DocumentsPaginator;
use Ecourier\Requests\Documents\GetDocumentsRequest;
use Ecourier\Requests\Documents\GetDocumentRequest;
use Ecourier\Requests\Documents\GetDocumentContentRequest;
use Ecourier\Requests\Documents\GetDocumentHtmlRequest;
use Ecourier\Requests\Documents\GetDocumentPdfRequest;
use Ecourier\Requests\Documents\MarkDocumentDeliveredRequest;
use Ecourier\Requests\Documents\SendDocumentAsJsonRequest;
use Ecourier\Requests\Documents\SendDocumentAsXmlRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class DocumentsResource extends BaseResource
{
    public function list(
        DocumentStatus|array|null $status = null,
        Channel|array|null $channel = null,
        string|array|null $companyId = null,
        Direction|array|null $direction = null,
        ?Sort $sort = null,
        int $perPage = 10,
    ): DocumentsPaginator {
        $request = new GetDocumentsRequest(
            status: $status,
            channel: $channel,
            companyId: $companyId,
            direction: $direction,
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

    public function sendJson(Channel $channel, InvoiceDocumentData $document): SendDocumentData
    {
        return $this->connector->send(new SendDocumentAsJsonRequest($channel, $document))->dto();
    }

    public function sendXml(
        string $xml,
        Channel $channel,
        IdentifierScheme $senderScheme,
        string $senderId,
        IdentifierScheme $recipientScheme,
        string $recipientId,
    ): SendDocumentData {
        return $this->connector->send(new SendDocumentAsXmlRequest(
            xml: $xml,
            channel: $channel,
            senderScheme: $senderScheme,
            senderId: $senderId,
            recipientScheme: $recipientScheme,
            recipientId: $recipientId,
        ))->dto();
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

    public function markDelivered(string $document, bool $delivered = true): DocumentData
    {
        return $this->connector->send(new MarkDocumentDeliveredRequest($document, $delivered))->dto();
    }
}
