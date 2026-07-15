<?php

declare(strict_types=1);

use Ecourier\Data\DocumentData;
use Ecourier\Data\Invoice\InvoiceDocumentData;
use Ecourier\Data\Invoice\InvoiceContactData;
use Ecourier\Data\Invoice\InvoiceLineData;
use Ecourier\Data\Invoice\InvoicePaymentAccountData;
use Ecourier\Data\Invoice\InvoicePaymentData;
use Ecourier\Data\Invoice\InvoicePaymentMeansData;
use Ecourier\Data\Invoice\InvoicePartyData;
use Ecourier\Data\Invoice\InvoiceTaxCategoryData;
use Ecourier\Data\Invoice\InvoiceTotalsData;
use Ecourier\Data\Invoice\ParticipantIdentifier;
use Ecourier\Data\SendDocumentData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\AccountSchemeId;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;
use Ecourier\Enums\PaymentMeansCode;
use Ecourier\Enums\Sort;
use Ecourier\Enums\SubmissionFormat;
use Ecourier\Enums\TaxCategoryCode;
use Ecourier\Pagination\DocumentsPaginator;
use Ecourier\Requests\Documents\GetDocumentRequest;
use Ecourier\Requests\Documents\GetDocumentsRequest;
use Ecourier\Requests\Documents\MarkDocumentDeliveredRequest;
use Ecourier\Requests\Documents\SendDocumentAsJsonRequest;
use Ecourier\Requests\Documents\SendDocumentAsXmlRequest;
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
        lines: [new InvoiceLineData(id: '1')],
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
    expect($document->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
    expect($document->status)->toBe(DocumentStatus::Delivered);
    expect($document->channel)->toBe(Channel::NemHandel);
    expect($document->mode)->toBe(Mode::Live);
    expect($document->direction)->toBe(Direction::Send);
    expect($document->type)->toBe(DocumentType::Invoice);
    expect($document->submissionFormat)->toBe(SubmissionFormat::JSON);
    expect($document->sender?->scheme)->toBe(IdentifierScheme::DK_CVR);
    expect($document->recipient?->identifier)->toBe('5790000123456');
    expect($document->latestE2eMessageUuid)->toBe('ddc3b3ef-cbd4-4630-9d65-896b3e1abc61');
    expect($document->latestE2eTransmissionId)->toBe('trans_01def');
    expect($document->company?->name)->toBe('Acme Danmark A/S');
});

// --- MarkDocumentDeliveredRequest ---

it('can mark a document as delivered', function () {
    $mockClient = new MockClient([
        MarkDocumentDeliveredRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/document.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $document = $connector->documents()->markDelivered('01kmkdaf55vrrecfy70180tpr6');

    expect($document)->toBeInstanceOf(DocumentData::class);
    expect($document->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
    $mockClient->assertSent(function (MarkDocumentDeliveredRequest $request): bool {
        return $request->resolveEndpoint() === '/documents/01kmkdaf55vrrecfy70180tpr6/delivered'
            && $request->body()->all() === ['delivered' => true];
    });
});

it('can move a document back to ready', function () {
    $request = new MarkDocumentDeliveredRequest('01kmkdaf55vrrecfy70180tpr6', delivered: false);

    expect($request->body()->all())->toBe(['delivered' => false]);
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
    expect($items[0]->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
    expect($items[0]->createdAt?->format('Y-m-d\TH:i:s\Z'))->toBe('2024-06-01T10:00:00Z');
    expect($items[0]->company?->name)->toBe('Acme Danmark A/S');
});

it('serializes enum filters into filter[] query params', function () {
    $request = new GetDocumentsRequest(
        status: [DocumentStatus::Pending],
        channel: [Channel::Peppol],
        companyId: ['0101knwp96k3ggvkra831yrd74zh'],
        direction: [Direction::Send],
    );

    $query = $request->query()->all();

    expect($query['filter']['channel'])->toBe(['Peppol']);
    expect($query['filter']['company_id'])->toBe(['0101knwp96k3ggvkra831yrd74zh']);
    expect($query['filter']['direction'])->toBe(['Send']);
    expect($query['filter']['status'])->toBe(['Pending']);
});

it('omits the filter key entirely when no filters are set', function () {
    $request = new GetDocumentsRequest();

    expect($request->query()->all())->not()->toHaveKey('filter');
});

it('serializes sort enum to its wire value', function () {
    $asc  = new GetDocumentsRequest(sort: Sort::CreatedAt);
    $desc = new GetDocumentsRequest(sort: Sort::CreatedAtDesc);

    expect($asc->query()->all()['sort'])->toBe('created_at');
    expect($desc->query()->all()['sort'])->toBe('-created_at');
});

it('omits sort when not provided', function () {
    $request = new GetDocumentsRequest();

    expect($request->query()->all())->not()->toHaveKey('sort');
});

// --- SendDocumentAsJsonRequest ---

it('can send a document as json', function () {
    $mockClient = new MockClient([
        SendDocumentAsJsonRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/send-document.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $document = $connector->documents()->sendJson(Channel::Peppol, minimalInvoice());

    expect($document)->toBeInstanceOf(SendDocumentData::class);
    expect($document->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
    expect($document->e2eMessageUuid)->toBe('ddc3b3ef-cbd4-4630-9d65-896b3e1abc61');
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

it('serializes invoice line fields from the api schema', function () {
    $line = new InvoiceLineData(
        id: '1',
        name: 'Consulting',
        description: 'Implementation work',
        quantity: '2',
        unitCode: 'HUR',
        unitPrice: '500.00',
        lineTotal: '1000.00',
        taxCategory: new InvoiceTaxCategoryData(
            code: TaxCategoryCode::S,
            percent: '25.00',
            exemptionReason: 'Reverse charge',
        ),
        itemId: 'item-1',
        sellersItemId: 'seller-1',
        buyersItemId: 'buyer-1',
    );

    expect($line->toArray())->toMatchArray([
        'id' => '1',
        'name' => 'Consulting',
        'description' => 'Implementation work',
        'quantity' => '2',
        'unit_code' => 'HUR',
        'unit_price' => '500.00',
        'line_total' => '1000.00',
        'tax_category' => [
            'code' => 'S',
            'percent' => '25.00',
            'exemption_reason' => 'Reverse charge',
        ],
        'item_id' => 'item-1',
        'sellers_item_id' => 'seller-1',
        'buyers_item_id' => 'buyer-1',
    ]);
});

it('serializes invoice party and payment fields from the api schema', function () {
    $party = new InvoicePartyData(
        participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '12345678'),
        name: 'Acme Danmark A/S',
        registrationNumber: '12345678',
        vatId: 'DK12345678',
        contact: new InvoiceContactData(
            name: 'Jane Doe',
            email: 'ap@example.com',
            phone: '+45 12 34 56 78',
        ),
    );

    $payment = new InvoicePaymentData(
        paymentMeans: [
            new InvoicePaymentMeansData(
                code: PaymentMeansCode::PaymentToAccount,
                id: 1,
                remittanceText: 'REF-1',
                instruction: 'Use invoice number',
                account: new InvoicePaymentAccountData(
                    id: 'DK12341234567890',
                    scheme: AccountSchemeId::IBAN,
                    bankId: 'DABADKKK',
                    bankName: 'Example Bank',
                ),
            ),
        ],
        paymentTermsNote: 'Net 30',
    );

    expect($party->toArray())->toMatchArray([
        'name' => 'Acme Danmark A/S',
        'registration_number' => '12345678',
        'vat_id' => 'DK12345678',
        'contact' => [
            'name' => 'Jane Doe',
            'email' => 'ap@example.com',
            'phone' => '+45 12 34 56 78',
        ],
    ]);

    expect($payment->toArray())->toMatchArray([
        'payment_terms_note' => 'Net 30',
        'payment_means' => [[
            'code' => '42',
            'id' => 1,
            'remittance_text' => 'REF-1',
            'instruction' => 'Use invoice number',
            'account' => [
                'id' => 'DK12341234567890',
                'scheme' => 'IBAN',
                'bank_id' => 'DABADKKK',
                'bank_name' => 'Example Bank',
            ],
        ]],
    ]);
});

// --- SendDocumentAsXmlRequest ---

it('can send a document as xml', function () {
    $mockClient = new MockClient([
        SendDocumentAsXmlRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/send-document.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $document = $connector->documents()->sendXml(
        xml: '<Invoice>...</Invoice>',
        channel: Channel::Peppol,
        senderScheme: IdentifierScheme::DK_CVR,
        senderId: '12345678',
        recipientScheme: IdentifierScheme::GLN,
        recipientId: '5790000123456',
    );

    expect($document)->toBeInstanceOf(SendDocumentData::class);
    expect($document->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
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
