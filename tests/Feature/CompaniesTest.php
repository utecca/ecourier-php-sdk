<?php

declare(strict_types=1);

use Ecourier\Data\CompanyData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Mode;
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
    expect($company->id)->toBe('0101knwp96k3ggvkra831yrd74zh');
    expect($company->name)->toBe('Acme Danmark A/S');
    expect($company->mode)->toBe(Mode::Live);
    expect($company->companyNo)->toBe('12345678');
    expect($company->country)->toBe('DK');
    expect($company->authorisation?->signed)->toBeFalse();
    expect($company->authorisation?->signer->firstName)->toBe('Pernille');
    expect($company->participants)->toHaveCount(1);
    expect($company->participants[0]->identifier)->toBe('5790000435944');
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
