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
use Ecourier\Requests\Companies\SubmitAuthorisationPdfRequest;
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
    expect($company->authorisation?->redirectUrl)->toBe('https://example.com/onboarding/authorisation-complete');
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
        redirectUrl: 'https://example.com/onboarding/authorisation-complete',
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
        'redirect_url' => 'https://example.com/onboarding/authorisation-complete',
    ]);
});

it('omits redirect url when not set', function () {
    $request = new CreateCompanyRequest(new CreateCompanyData(
        name: 'Acme Danmark A/S',
        country: 'DK',
        companyNo: '12345678',
        signer: new CompanyAuthorisationSignerData('Ada', 'Lovelace', 'CEO'),
    ));

    expect($request->body()->all())->not->toHaveKey('redirect_url');
});

it('omits the signer when not set', function () {
    $request = new CreateCompanyRequest(new CreateCompanyData(
        name: 'Acme Danmark A/S',
        country: 'DK',
        companyNo: '12345678',
    ));

    expect($request->body()->all())->toBe([
        'name' => 'Acme Danmark A/S',
        'country' => 'DK',
        'company_no' => '12345678',
    ]);
});

it('can submit a signed authorisation pdf', function () {
    $mockClient = new MockClient([
        SubmitAuthorisationPdfRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/company.json'),
            status: 201,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $company = $connector->companies()->submitAuthorisationPdf(
        company: '0101knwp96k3ggvkra831yrd74zh',
        pdf: '%PDF-1.4',
        signer: new CompanyAuthorisationSignerData('Ada', 'Lovelace', 'CEO'),
    );

    expect($company)->toBeInstanceOf(CompanyData::class);
    $mockClient->assertSent(function (SubmitAuthorisationPdfRequest $request, $response): bool {
        $pending = $response->getPendingRequest();

        return $request->resolveEndpoint() === '/companies/0101knwp96k3ggvkra831yrd74zh/authorisation/pdf'
            && str_starts_with($pending->headers()->get('Content-Type'), 'multipart/form-data; boundary=');
    });
});

it('serializes the authorisation pdf as multipart fields', function () {
    $request = new SubmitAuthorisationPdfRequest(
        company: '0101knwp96k3ggvkra831yrd74zh',
        pdf: '%PDF-1.4',
        signer: new CompanyAuthorisationSignerData('Ada', 'Lovelace', 'CEO'),
    );

    $fields = [];
    foreach ($request->body()->all() as $value) {
        $fields[$value->name] = [$value->value, $value->filename];
    }

    expect($fields)->toBe([
        'pdf' => ['%PDF-1.4', 'authorisation.pdf'],
        'signer[first_name]' => ['Ada', null],
        'signer[last_name]' => ['Lovelace', null],
        'signer[title]' => ['CEO', null],
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
