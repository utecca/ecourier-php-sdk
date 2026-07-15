<?php

declare(strict_types=1);

use Ecourier\Data\Invoice\InvoiceDocumentData;
use Ecourier\Data\Invoice\InvoiceLineData;
use Ecourier\Data\Invoice\InvoicePartyData;
use Ecourier\Data\Invoice\InvoiceTotalsData;
use Ecourier\Data\Invoice\ParticipantIdentifier;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Exceptions\AuthenticationException;
use Ecourier\Exceptions\NotFoundException;
use Ecourier\Exceptions\ValidationException;
use Ecourier\Requests\Companies\GetCompanyRequest;
use Ecourier\Requests\Documents\SendDocumentAsJsonRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('throws authentication exception on 401', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: json_encode(['message' => 'Unauthenticated.']),
            status: 401,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_invalid');
    $connector->withMockClient($mockClient);

    $connector->companies()->get('comp_01abc');
})->throws(AuthenticationException::class);

it('throws not found exception on 404', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: json_encode(['message' => 'Not found.']),
            status: 404,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $connector->companies()->get('comp_nonexistent');
})->throws(NotFoundException::class);

it('throws validation exception on 422 with errors', function () {
    $mockClient = new MockClient([
        SendDocumentAsJsonRequest::class => MockResponse::make(
            body: json_encode([
                'message' => 'Validation failed.',
                'errors' => ['type' => ['The type field is required.']],
            ]),
            status: 422,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $invoice = new InvoiceDocumentData(
        type: DocumentType::Invoice,
        id: 'INV-001',
        issueDate: '2024-06-01',
        currency: Currency::DKK,
        supplier: new InvoicePartyData(participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '12345678')),
        customer: new InvoicePartyData(participant: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '87654321')),
        lines: [new InvoiceLineData(id: '1')],
        totals: new InvoiceTotalsData('1000.00', '250.00', '1250.00'),
    );

    try {
        $connector->documents()->sendJson(Channel::Peppol, $invoice);
    } catch (ValidationException $e) {
        expect($e->getErrors())->toHaveKey('type');
        expect($e->getMessage())->toBe('Validation failed.');
        throw $e;
    }
})->throws(ValidationException::class);
