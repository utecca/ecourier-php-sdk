<?php

declare(strict_types=1);

use Ecourier\Sdk\EcourierConnector;
use Ecourier\Sdk\Exceptions\AuthenticationException;
use Ecourier\Sdk\Exceptions\NotFoundException;
use Ecourier\Sdk\Exceptions\ValidationException;
use Ecourier\Sdk\Requests\Companies\GetCompanyRequest;
use Ecourier\Sdk\Requests\Documents\SendDocumentAsJsonRequest;
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

    try {
        $connector->documents()->sendJson([]);
    } catch (ValidationException $e) {
        expect($e->getErrors())->toHaveKey('type');
        expect($e->getMessage())->toBe('Validation failed.');
        throw $e;
    }
})->throws(ValidationException::class);
