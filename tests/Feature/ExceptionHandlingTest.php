<?php

declare(strict_types=1);

use Ecourier\Data\Requests\Invoice\InvoiceDocumentData;
use Ecourier\Data\Requests\Invoice\InvoiceLineData;
use Ecourier\Data\Requests\Invoice\InvoicePartyData;
use Ecourier\Data\Requests\Invoice\InvoiceTotalsData;
use Ecourier\Data\Requests\Invoice\ParticipantIdentifier;
use Ecourier\Data\Requests\Invoice\SimplifiedAddressData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\InvoiceDocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Exceptions\AuthenticationException;
use Ecourier\Exceptions\BadRequestException;
use Ecourier\Exceptions\EcourierException;
use Ecourier\Exceptions\FailedDependencyException;
use Ecourier\Exceptions\ForbiddenException;
use Ecourier\Exceptions\GoneException;
use Ecourier\Exceptions\NotFoundException;
use Ecourier\Exceptions\PaymentRequiredException;
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
        type: InvoiceDocumentType::Invoice,
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
        lines: [new InvoiceLineData(id: 1, name: 'Consulting')],
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
    [402, PaymentRequiredException::class],
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

it('throws an ecourier exception for json error bodies that are not an object', function (string $body) {
    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient(new MockClient([
        GetCompanyRequest::class => MockResponse::make(body: $body, status: 502),
    ]));

    try {
        $connector->companies()->get('comp_01abc');
    } catch (EcourierException $e) {
        expect($e->getMessage())->toBe('Request failed with status 502.')
            ->and($e->getErrorCode())->toBeNull();

        return;
    }

    test()->fail('Expected an EcourierException.');
})->with([
    'null' => ['null'],
    'a string' => ['"Bad Gateway"'],
    'a number' => ['123'],
]);

it('falls back to a default message when the error message is not a string', function (int $status, array $body, string $exception, string $message) {
    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient(new MockClient([
        GetCompanyRequest::class => MockResponse::make(body: $body, status: $status),
    ]));

    try {
        $connector->companies()->get('comp_01abc');
    } catch (EcourierException $e) {
        expect($e)->toBeInstanceOf($exception)
            ->and($e->getMessage())->toBe($message);

        return;
    }

    test()->fail('Expected an EcourierException.');
})->with([
    'message is an array' => [500, ['message' => ['Server error.']], EcourierException::class, 'Request failed with status 500.'],
    'message is a number' => [500, ['message' => 5], EcourierException::class, 'Request failed with status 500.'],
    'error is used when message is not a string' => [500, ['message' => ['x'], 'error' => 'Server error.'], EcourierException::class, 'Server error.'],
    'validation message is an array' => [422, ['message' => ['x']], ValidationException::class, 'Validation failed.'],
]);

it('has no validation errors when the errors field is not an array', function () {
    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient(new MockClient([
        GetCompanyRequest::class => MockResponse::make(body: ['message' => 'Validation failed.', 'errors' => 'nope'], status: 422),
    ]));

    try {
        $connector->companies()->get('comp_01abc');
    } catch (ValidationException $e) {
        expect($e->getErrors())->toBe([])
            ->and($e->getMessage())->toBe('Validation failed.');

        return;
    }

    test()->fail('Expected a ValidationException.');
});

it('exposes the api error code', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: json_encode([
                'code' => 'Billing.InsufficientBalance',
                'message' => 'Insufficient balance. Top up your balance to send documents.',
            ]),
            status: 402,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    try {
        $connector->companies()->get('comp_01abc');
    } catch (PaymentRequiredException $e) {
        expect($e->getErrorCode())->toBe('Billing.InsufficientBalance');

        return;
    }

    $this->fail('Expected a PaymentRequiredException.');
});

it('has no error code when the api gives none', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: '<html><body>502 Bad Gateway</body></html>',
            status: 502,
            headers: ['Content-Type' => 'text/html'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    try {
        $connector->companies()->get('comp_01abc');
    } catch (EcourierException $e) {
        expect($e->getErrorCode())->toBeNull();

        return;
    }

    $this->fail('Expected an EcourierException.');
});
