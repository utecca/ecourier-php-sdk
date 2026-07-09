<?php

declare(strict_types=1);

use Ecourier\Data\CompanyData;
use Ecourier\EcourierConnector;
use Ecourier\Requests\Companies\GetCompanyRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get a company', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/company.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $company = $connector->companies()->find('comp_01abc');

    expect($company)->toBeInstanceOf(CompanyData::class);
    expect($company->id)->toBe('comp_01abc');
    expect($company->name)->toBe('Acme Corporation');
    expect($company->cvr)->toBe('12345678');
    expect($company->country)->toBe('DK');
    expect($company->address)->not()->toBeNull();
    expect($company->address->city)->toBe('Copenhagen');
});

it('sends the correct request to get a company', function () {
    $mockClient = new MockClient([
        GetCompanyRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/company.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $connector->companies()->get('comp_01abc');

    $mockClient->assertSent(GetCompanyRequest::class);
});
