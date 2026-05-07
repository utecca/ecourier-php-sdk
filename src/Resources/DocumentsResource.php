<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Resources;

use Ecourier\Sdk\Data\DocumentData;
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
        ?string $status = null,
        ?string $direction = null,
        ?string $from = null,
        ?string $to = null,
        int $perPage = 25,
    ): DocumentsPaginator {
        $request = new GetDocumentsRequest(
            status: $status,
            direction: $direction,
            from: $from,
            to: $to,
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

    public function sendJson(array $payload): Response
    {
        return $this->connector->send(new SendDocumentAsJsonRequest($payload));
    }

    public function sendXml(string $xml): Response
    {
        return $this->connector->send(new SendDocumentAsXmlRequest($xml));
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
