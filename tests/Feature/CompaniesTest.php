<?php

declare(strict_types=1);

use Ecourier\Data\CompanyData;
use Ecourier\Data\CompanyListItemData;
use Ecourier\Data\CompanyAuthorisationSignerData;
use Ecourier\Data\CreateCompanyData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Mode;
use Ecourier\Pagination\CompaniesPaginator;
use Ecourier\Requests\Companies\CreateCompanyRequest;
use Ecourier\Requests\Companies\DeleteCompanyRequest;
use Ecourier\Requests\Companies\GetCompaniesRequest;
use Ecourier\Requests\Companies\GetCompanyRequest;
use Ecourier\Requests\Companies\UpdateCompanyRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns a paginator when listing companies', function () {
    $mockClient = new MockClient([
        GetCompaniesRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/companies-paginated.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $paginator = $connector->companies()->list();

    expect($paginator)->toBeInstanceOf(CompaniesPaginator::class);
});

it('can collect paginated companies', function () {
    $mockClient = new MockClient([
        GetCompaniesRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/companies-paginated.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $items = iterator_to_array($connector->companies()->list()->items());

    expect($items)->toHaveCount(1);
    expect($items[0])->toBeInstanceOf(CompanyListItemData::class);
    expect($items[0]->companyNo)->toBe('12345678');
});

it('serializes company list filters into filter query params', function () {
    $request = new GetCompaniesRequest(
        channel: [Channel::Peppol],
        companyNo: ['12345678'],
        country: ['DK'],
        name: ['Acme'],
        signed: false,
    );

    $query = $request->query()->all();

    expect($query['filter']['channel'])->toBe(['Peppol']);
    expect($query['filter']['company_no'])->toBe(['12345678']);
    expect($query['filter']['country'])->toBe(['DK']);
    expect($query['filter']['name'])->toBe(['Acme']);
    expect($query['filter']['signed'])->toBeFalse();
});

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

it('can create a company', function () {
    $mockClient = new MockClient([
        CreateCompanyRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/company.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $company = $connector->companies()->create(new CreateCompanyData(
        name: 'Acme Danmark A/S',
        country: 'DK',
        companyNo: '12345678',
        signer: new CompanyAuthorisationSignerData('Ada', 'Lovelace', 'CEO'),
    ));

    expect($company)->toBeInstanceOf(CompanyData::class);
    expect($company->companyNo)->toBe('12345678');
    $mockClient->assertSent(CreateCompanyRequest::class);
});

it('serializes create company body from the api schema', function () {
    $request = new CreateCompanyRequest(new CreateCompanyData(
        name: 'Acme Danmark A/S',
        country: 'DK',
        companyNo: '12345678',
        signer: new CompanyAuthorisationSignerData('Ada', 'Lovelace', 'CEO'),
        parentId: '0101knwp96k3ggvkra831yrd75abc',
    ));

    expect($request->body()->all())->toBe([
        'name' => 'Acme Danmark A/S',
        'country' => 'DK',
        'company_no' => '12345678',
        'signer' => [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'title' => 'CEO',
        ],
        'parent_id' => '0101knwp96k3ggvkra831yrd75abc',
    ]);
});

it('can update a company', function () {
    $mockClient = new MockClient([
        UpdateCompanyRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/company.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $company = $connector->companies()->update('0101knwp96k3ggvkra831yrd74zh', 'Acme Danmark A/S');

    expect($company)->toBeInstanceOf(CompanyData::class);
    $mockClient->assertSent(UpdateCompanyRequest::class);
});

it('serializes update company body from the api schema', function () {
    $request = new UpdateCompanyRequest('0101knwp96k3ggvkra831yrd74zh', 'Acme Danmark A/S');

    expect($request->resolveEndpoint())->toBe('/companies/0101knwp96k3ggvkra831yrd74zh');
    expect($request->body()->all())->toBe(['name' => 'Acme Danmark A/S']);
});

it('can delete a company', function () {
    $mockClient = new MockClient([
        DeleteCompanyRequest::class => MockResponse::make(status: 204),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $response = $connector->companies()->delete('0101knwp96k3ggvkra831yrd74zh');

    expect($response->status())->toBe(204);
    $mockClient->assertSent(DeleteCompanyRequest::class);
});
