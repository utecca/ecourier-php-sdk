<?php

declare(strict_types=1);

use Ecourier\Data\Invoice\InvoiceDocumentData;
use Ecourier\Data\Invoice\InvoiceLineData;
use Ecourier\Data\Invoice\InvoicePartyData;
use Ecourier\Data\Invoice\InvoiceTotalsData;
use Ecourier\Data\Invoice\ParticipantIdentifier;
use Ecourier\Data\Invoice\SimplifiedAddressData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Exceptions\AuthenticationException;
use Ecourier\Exceptions\BadRequestException;
use Ecourier\Exceptions\EcourierException;
use Ecourier\Exceptions\FailedDependencyException;
use Ecourier\Exceptions\ForbiddenException;
use Ecourier\Exceptions\GoneException;
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

it('maps error statuses to exceptions', function (int $status, string $exception) {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: json_encode(['message' => 'Something went wrong.']),
            status: $status,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    try {
        $connector->companies()->get('comp_01abc');
    } catch (EcourierException $e) {
        expect($e)->toBeInstanceOf($exception);
        expect($e->getMessage())->toBe('Something went wrong.');
        expect($e->getCode())->toBe($status);

        return;
    }

    test()->fail('Expected an exception.');
})->with([
    [400, BadRequestException::class],
    [403, ForbiddenException::class],
    [410, GoneException::class],
    [424, FailedDependencyException::class],
    [500, EcourierException::class],
]);

it('throws an ecourier exception for non-json error bodies', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: '<html><body>502 Bad Gateway</body></html>',
            status: 502,
            headers: ['Content-Type' => 'text/html'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $connector->companies()->get('comp_01abc');
})->throws(EcourierException::class, 'Request failed with status 502.');
