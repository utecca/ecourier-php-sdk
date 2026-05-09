<?php

declare(strict_types=1);

use Ecourier\Sdk\Data\DocumentData;
use Ecourier\Sdk\Data\Invoice\InvoiceDocumentData;
use Ecourier\Sdk\Data\Invoice\InvoiceLineData;
use Ecourier\Sdk\Data\Invoice\InvoicePartyData;
use Ecourier\Sdk\Data\Invoice\InvoiceTotalsData;
use Ecourier\Sdk\Data\Invoice\ParticipantIdentifier;
use Ecourier\Sdk\EcourierConnector;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\Currency;
use Ecourier\Sdk\Enums\Direction;
use Ecourier\Sdk\Enums\DocumentStatus;
use Ecourier\Sdk\Enums\DocumentType;
use Ecourier\Sdk\Enums\IdentifierScheme;
use Ecourier\Sdk\Pagination\DocumentsPaginator;
use Ecourier\Sdk\Requests\Documents\GetDocumentRequest;
use Ecourier\Sdk\Requests\Documents\GetDocumentsRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsJsonRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsXmlRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function minimalInvoice(): InvoiceDocumentData
{
    return new InvoiceDocumentData(
        type: DocumentType::Invoice,
        id: 'INV-2024-001',
        issueDate: '2024-06-01',
        currency: Currency::DKK,
        supplier: new InvoicePartyData(
            participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '12345678'),
        ),
        customer: new InvoicePartyData(
            participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '87654321'),
        ),
        lines: [new InvoiceLineData(id: 1)],
        totals: new InvoiceTotalsData(
            subtotalAmount: '1000.00',
            taxAmount: '250.00',
            totalAmount: '1250.00',
        ),
    );
}

// --- Document DTO ---

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
    expect($document->status)->toBe(DocumentStatus::Delivered);
    expect($document->direction)->toBe(Direction::Send);
    expect($document->type)->toBe(DocumentType::Invoice);
    expect($document->channel)->toBe(Channel::NemHandel);
    expect($document->totalAmount)->toBe(1250.0);
    expect($document->currency)->toBe(Currency::DKK);
    expect($document->sender)->not()->toBeNull();
    expect($document->sender->name)->toBe('Acme Corporation');
    expect($document->receiver)->not()->toBeNull();
    expect($document->receiver->name)->toBe('Beta Ltd');
});

it('maps unrecognised optional enum values to null', function () {
    $document = DocumentData::fromArray([
        'id' => 'doc_future',
        'status' => 'Delivered',
        'direction' => 'Send',
        'type' => 'NewDocumentType',
        'channel' => 'NewChannel',
        'currency' => 'XYZ',
    ]);

    expect($document->type)->toBeNull();
    expect($document->channel)->toBeNull();
    expect($document->currency)->toBeNull();
});

// --- GetDocumentsRequest ---

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

it('serializes enum filters into filter[] query params', function () {
    $request = new GetDocumentsRequest(
        status: DocumentStatus::Pending,
        createdAt: '2024-06-01',
        identityId: 'DK:CVR:12345678',
    );

    $query = $request->query()->all();

    expect($query['filter']['status'])->toBe('Pending');
    expect($query['filter']['created_at'])->toBe('2024-06-01');
    expect($query['filter']['identity_id'])->toBe('DK:CVR:12345678');
});

it('omits the filter key entirely when no filters are set', function () {
    $request = new GetDocumentsRequest();

    expect($request->query()->all())->not()->toHaveKey('filter');
});

// --- SendDocumentAsJsonRequest ---

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

    $response = $connector->documents()->sendJson(Channel::Peppol, minimalInvoice());

    expect($response->status())->toBe(200);
    $mockClient->assertSent(SendDocumentAsJsonRequest::class);
});

it('serializes channel and document into the json body', function () {
    $request = new SendDocumentAsJsonRequest(Channel::NemHandel, minimalInvoice());

    $body = $request->body()->all();

    expect($body['channel'])->toBe('NemHandel');
    expect($body['document']['type'])->toBe('Invoice');
    expect($body['document']['currency'])->toBe('DKK');
    expect($body['document']['supplier']['participant']['scheme'])->toBe('DK:CVR');
});

// --- SendDocumentAsXmlRequest ---

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

    $response = $connector->documents()->sendXml(
        xml: '<Invoice>...</Invoice>',
        channel: Channel::Peppol,
        senderScheme: IdentifierScheme::DK_CVR,
        senderId: '12345678',
        recipientScheme: IdentifierScheme::GLN,
        recipientId: '5790000123456',
    );

    expect($response->status())->toBe(200);
    $mockClient->assertSent(SendDocumentAsXmlRequest::class);
});

it('sets all required routing headers when sending xml', function () {
    $request = new SendDocumentAsXmlRequest(
        xml: '<Invoice/>',
        channel: Channel::Peppol,
        senderScheme: IdentifierScheme::DK_CVR,
        senderId: '12345678',
        recipientScheme: IdentifierScheme::GLN,
        recipientId: '5790000123456',
    );

    $headers = $request->headers()->all();

    expect($headers['Ecourier-Channel'])->toBe('Peppol');
    expect($headers['Ecourier-Sender-Scheme'])->toBe('DK:CVR');
    expect($headers['Ecourier-Sender-Id'])->toBe('12345678');
    expect($headers['Ecourier-Recipient-Scheme'])->toBe('GLN');
    expect($headers['Ecourier-Recipient-Id'])->toBe('5790000123456');
});
