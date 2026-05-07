<?php

declare(strict_types=1);

use Ecourier\Sdk\Data\DocumentData;
use Ecourier\Sdk\EcourierConnector;
use Ecourier\Sdk\Pagination\DocumentsPaginator;
use Ecourier\Sdk\Requests\Documents\GetDocumentRequest;
use Ecourier\Sdk\Requests\Documents\GetDocumentsRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsJsonRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsXmlRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get a document', function () {
    $mockClient = new MockClient([
        GetDocumentRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/document.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $document = $connector->documents()->find('doc_01xyz');

    expect($document)->toBeInstanceOf(DocumentData::class);
    expect($document->id)->toBe('doc_01xyz');
    expect($document->status)->toBe('delivered');
    expect($document->direction)->toBe('outbound');
    expect($document->totalAmount)->toBe(1250.0);
    expect($document->currency)->toBe('DKK');
    expect($document->sender)->not()->toBeNull();
    expect($document->sender->name)->toBe('Acme Corporation');
    expect($document->receiver)->not()->toBeNull();
    expect($document->receiver->name)->toBe('Beta Ltd');
});

it('returns a paginator when listing documents', function () {
    $mockClient = new MockClient([
        GetDocumentsRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/documents-paginated.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $paginator = $connector->documents()->list();

    expect($paginator)->toBeInstanceOf(DocumentsPaginator::class);
});

it('can collect paginated documents', function () {
    $mockClient = new MockClient([
        GetDocumentsRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/documents-paginated.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $items = iterator_to_array($connector->documents()->list()->items());

    expect($items)->toHaveCount(1);
    expect($items[0])->toBeInstanceOf(DocumentData::class);
    expect($items[0]->id)->toBe('doc_01xyz');
});

it('can send a document as json', function () {
    $mockClient = new MockClient([
        SendDocumentAsJsonRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/document.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $response = $connector->documents()->sendJson([
        'type' => 'invoice',
        'reference' => 'INV-2024-001',
    ]);

    expect($response->status())->toBe(200);
    $mockClient->assertSent(SendDocumentAsJsonRequest::class);
});

it('can send a document as xml', function () {
    $mockClient = new MockClient([
        SendDocumentAsXmlRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/document.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $response = $connector->documents()->sendXml('<Invoice>...</Invoice>');

    expect($response->status())->toBe(200);
    $mockClient->assertSent(SendDocumentAsXmlRequest::class);
});
