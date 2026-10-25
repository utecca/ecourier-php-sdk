<?php

declare(strict_types=1);

use Ecourier\Data\DocumentData;
use Ecourier\Data\DocumentListItemData;
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
use Ecourier\Data\Invoice\SimplifiedAddressData;
use Ecourier\Data\SendDocumentData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\AccountSchemeId;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Locale;
use Ecourier\Enums\Mode;
use Ecourier\Enums\PaymentMeansCode;
use Ecourier\Enums\SubmissionFormat;
use Ecourier\Enums\TaxCategoryCode;
use Ecourier\Pagination\DocumentsPaginator;
use Ecourier\Requests\Documents\GetDocumentHtmlRequest;
use Ecourier\Requests\Documents\GetDocumentPdfRequest;
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
            name: 'Acme Danmark A/S',
            simplifiedAddress: new SimplifiedAddressData('Vestergade 1', 'Copenhagen', '1456', 'DK'),
        ),
        customer: new InvoicePartyData(
            participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '87654321'),
            name: 'Customer ApS',
            simplifiedAddress: new SimplifiedAddressData('Nørregade 2', 'Aarhus', '8000', 'DK'),
        ),
        lines: [new InvoiceLineData(id: '1', name: 'Consulting')],
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
    expect($document->dashboardUrl)->toBe('https://app.ecourier.io/live/acme-danmark/documents/01kmkdaf55vrrecfy70180tpr6');
    expect($document->transmittedAt?->format('Y-m-d\TH:i:s\Z'))->toBe('2024-06-01T10:05:00Z');
    expect($document->ubl?->id)->toBe('INV-2024-001');
    expect($document->validation?->hasErrors())->toBeFalse();
    expect($document->validation?->hasWarnings())->toBeTrue();
    expect($document->validation?->warnings[0]->code)->toBe('W-INV181');
    expect($document->validation?->warnings[0]->message)->toBe('Note should not be longer than 500 characters');
    expect($document->validation?->warnings[0]->location)->toBe('/Invoice/cbc:Note');
});

it('can get a reminder document', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/document.json'), true);
    $fixture['type'] = 'Reminder';

    expect(DocumentData::fromArray($fixture)->type)->toBe(DocumentType::Reminder);
});


it('rejects document types that cannot be sent as JSON', function () {
    new InvoiceDocumentData(
        type: DocumentType::ApplicationResponse,
        id: minimalInvoice()->id,
        issueDate: minimalInvoice()->issueDate,
        currency: minimalInvoice()->currency,
        supplier: minimalInvoice()->supplier,
        customer: minimalInvoice()->customer,
        lines: minimalInvoice()->lines,
        totals: minimalInvoice()->totals,
    );
})->throws(InvalidArgumentException::class, 'Document type ApplicationResponse cannot be sent as JSON');

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
    expect($items[0])->toBeInstanceOf(DocumentListItemData::class);
    expect($items[0]->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
    expect($items[0]->createdAt?->format('Y-m-d\TH:i:s\Z'))->toBe('2024-06-01T10:00:00Z');
    expect($items[0]->company?->name)->toBe('Acme Danmark A/S');
});

it('follows pagination using the meta block', function () {
    $page = function (int $currentPage, string $id): MockResponse {
        $body = json_decode(file_get_contents(__DIR__ . '/../Fixtures/documents-paginated.json'), true);
        $body['data'][0]['id'] = $id;
        $body['meta']['current_page'] = $currentPage;
        $body['meta']['last_page'] = 2;
        $body['meta']['next_page_url'] = $currentPage < 2 ? 'https://api.ecourier.io/v1/documents?page=2' : null;

        return MockResponse::make(body: json_encode($body), status: 200, headers: ['Content-Type' => 'application/json']);
    };

    $mockClient = new MockClient([$page(1, 'doc_page_1'), $page(2, 'doc_page_2')]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $ids = array_map(fn(DocumentListItemData $item) => $item->id, iterator_to_array($connector->documents()->list()->items(), false));

    expect($ids)->toBe(['doc_page_1', 'doc_page_2']);
    $mockClient->assertSentCount(2);
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

it('only sends per_page when no filters are set', function () {
    $request = new GetDocumentsRequest();

    expect($request->query()->all())->toBe(['per_page' => 10]);
});

it('sends the locale as a query param when rendering', function () {
    $html = new GetDocumentHtmlRequest('doc_01xyz', Locale::DA);
    $pdf  = new GetDocumentPdfRequest('doc_01xyz', Locale::DA);

    expect($html->query()->all())->toBe(['locale' => 'da']);
    expect($pdf->query()->all())->toBe(['locale' => 'da']);
});

it('omits the locale when rendering without one', function () {
    expect((new GetDocumentHtmlRequest('doc_01xyz'))->query()->all())->toBe([]);
    expect((new GetDocumentPdfRequest('doc_01xyz'))->query()->all())->toBe([]);
});

it('can render a document as pdf in a locale', function () {
    $mockClient = new MockClient([
        GetDocumentPdfRequest::class => MockResponse::make(
            body: '%PDF-1.4',
            status: 200,
            headers: ['Content-Type' => 'application/pdf'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $pdf = $connector->documents()->renderAsPdf('doc_01xyz', Locale::DA)->body();

    expect($pdf)->toBe('%PDF-1.4');
    $mockClient->assertSent(fn(GetDocumentPdfRequest $request, $response) => $response->getPendingRequest()->query()->get('locale') === 'da');
});

// --- SendDocumentAsJsonRequest ---

it('can send a document as json', function () {
    $mockClient = new MockClient([
        SendDocumentAsJsonRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/send-document.json'),
            status: 201,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $document = $connector->documents()->sendJson(Channel::Peppol, minimalInvoice());

    expect($document)->toBeInstanceOf(SendDocumentData::class);
    expect($document->id)->toBe('01kmkdaf55vrrecfy70180tpr6');
    $mockClient->assertSent(fn(SendDocumentAsJsonRequest $request, $response) => $response->getPendingRequest()->headers()->get('Content-Type') === 'application/json');
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

it('rejects payment means with an account scheme the code does not allow', function () {
    new InvoicePaymentMeansData(
        code: PaymentMeansCode::PaymentToAccount,
        account: new InvoicePaymentAccountData(id: 'DK5000400440116243', scheme: AccountSchemeId::IBAN),
    );
})->throws(InvalidArgumentException::class, 'Payment means code 42 requires the DK:BBAN account scheme.');

it('requires a bank id for payment means code 31', function () {
    new InvoicePaymentMeansData(
        code: PaymentMeansCode::DebitTransfer,
        account: new InvoicePaymentAccountData(id: 'DK5000400440116243', scheme: AccountSchemeId::IBAN),
    );
})->throws(InvalidArgumentException::class, 'requires a bank ID');

it('requires a bank id for DK:BBAN accounts', function () {
    new InvoicePaymentMeansData(
        code: PaymentMeansCode::PaymentToAccount,
        account: new InvoicePaymentAccountData(id: '1234567890', scheme: AccountSchemeId::DK_BBAN),
    );
})->throws(InvalidArgumentException::class, 'requires a bank ID');

it('accepts a credit transfer to an IBAN without a bank id', function () {
    $means = new InvoicePaymentMeansData(
        code: PaymentMeansCode::CreditTransfer,
        account: new InvoicePaymentAccountData(id: 'DK5000400440116243', scheme: AccountSchemeId::IBAN),
    );

    expect($means->toArray())->toBe([
        'code' => '30',
        'account' => ['id' => 'DK5000400440116243', 'scheme' => 'IBAN'],
    ]);
});

it('serializes invoice party and payment fields from the api schema', function () {
    $party = new InvoicePartyData(
        participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '12345678'),
        name: 'Acme Danmark A/S',
        simplifiedAddress: new SimplifiedAddressData('Vestergade 1', 'Copenhagen', '1456', 'DK'),
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
                code: PaymentMeansCode::DebitTransfer,
                account: new InvoicePaymentAccountData(
                    id: 'DK12341234567890',
                    scheme: AccountSchemeId::IBAN,
                    bankId: 'DABADKKK',
                    bankName: 'Example Bank',
                ),
                id: 1,
                remittanceText: 'REF-1',
            ),
        ],
        paymentTermsNote: 'Net 30',
    );

    expect($party->toArray())->toMatchArray([
        'name' => 'Acme Danmark A/S',
        'registration_number' => '12345678',
        'vat_id' => 'DK12345678',
        'simplified_address' => [
            'street_name' => 'Vestergade 1',
            'city' => 'Copenhagen',
            'postal_code' => '1456',
            'country' => 'DK',
        ],
        'contact' => [
            'name' => 'Jane Doe',
            'email' => 'ap@example.com',
            'phone' => '+45 12 34 56 78',
        ],
    ]);

    expect($payment->toArray())->toMatchArray([
        'payment_terms_note' => 'Net 30',
        'payment_means' => [[
            'code' => '31',
            'id' => 1,
            'remittance_text' => 'REF-1',
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
            status: 201,
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
