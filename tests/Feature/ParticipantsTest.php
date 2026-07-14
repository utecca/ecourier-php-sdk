<?php

declare(strict_types=1);

use Ecourier\Data\CreateParticipantData;
use Ecourier\Data\ParticipantData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;
use Ecourier\Pagination\ParticipantsPaginator;
use Ecourier\Requests\Participants\CreateParticipantRequest;
use Ecourier\Requests\Participants\DeleteParticipantRequest;
use Ecourier\Requests\Participants\GetParticipantRequest;
use Ecourier\Requests\Participants\GetParticipantsRequest;
use Ecourier\Requests\Participants\UpdateParticipantRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns a paginator when listing participants', function () {
    $mockClient = new MockClient([
        GetParticipantsRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participants-paginated.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $paginator = $connector->participants()->list();

    expect($paginator)->toBeInstanceOf(ParticipantsPaginator::class);
});

it('can collect paginated participants', function () {
    $mockClient = new MockClient([
        GetParticipantsRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participants-paginated.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $items = iterator_to_array($connector->participants()->list()->items());

    expect($items)->toHaveCount(1);
    expect($items[0])->toBeInstanceOf(ParticipantData::class);
    expect($items[0]->identifier)->toBe('5790000435944');
});

it('serializes participant list filters into filter query params', function () {
    $request = new GetParticipantsRequest(
        companyId: ['0101knwp96k3ggvkra831yrd74zh'],
        scheme: [IdentifierScheme::GLN],
        channel: [Channel::NemHandel],
    );

    $query = $request->query()->all();

    expect($query['filter']['company_id'])->toBe(['0101knwp96k3ggvkra831yrd74zh']);
    expect($query['filter']['scheme'])->toBe(['GLN']);
    expect($query['filter']['channel'])->toBe(['NemHandel']);
});

it('can get a participant', function () {
    $mockClient = new MockClient([
        GetParticipantRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participant.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $participant = $connector->participants()->find('0101knwp96k3ggvkra831yrd77ghi');

    expect($participant)->toBeInstanceOf(ParticipantData::class);
    expect($participant->id)->toBe('0101knwp96k3ggvkra831yrd77ghi');
    expect($participant->company->id)->toBe('0101knwp96k3ggvkra831yrd74zh');
    expect($participant->company->name)->toBe('Acme Danmark A/S');
    expect($participant->mode)->toBe(Mode::Live);
    expect($participant->scheme)->toBe(IdentifierScheme::GLN);
    expect($participant->identifier)->toBe('5790000435944');
    expect($participant->fullIdentifier)->toBe('0088:5790000435944');
    expect($participant->channels)->toBe([Channel::NemHandel]);
});

it('sends the correct request to get a participant', function () {
    $mockClient = new MockClient([
        GetParticipantRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participant.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $connector->participants()->get('0101knwp96k3ggvkra831yrd77ghi');

    $mockClient->assertSent(GetParticipantRequest::class);
});

it('can create a participant', function () {
    $mockClient = new MockClient([
        CreateParticipantRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participant.json'),
            status: 201,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $participant = $connector->participants()->create(new CreateParticipantData(
        companyId: '0101knwp96k3ggvkra831yrd74zh',
        scheme: IdentifierScheme::GLN,
        channels: [Channel::NemHandel],
    ));

    expect($participant)->toBeInstanceOf(ParticipantData::class);
    expect($participant->fullIdentifier)->toBe('0088:5790000435944');
    $mockClient->assertSent(CreateParticipantRequest::class);
});

it('serializes create participant body from the api schema', function () {
    $request = new CreateParticipantRequest(new CreateParticipantData(
        companyId: '0101knwp96k3ggvkra831yrd74zh',
        scheme: IdentifierScheme::DK_CVR,
        channels: [Channel::NemHandel],
        identifier: '12345678',
    ));

    expect($request->body()->all())->toBe([
        'company_id' => '0101knwp96k3ggvkra831yrd74zh',
        'scheme' => 'DK:CVR',
        'identifier' => '12345678',
        'channels' => ['NemHandel'],
    ]);
});

it('leaves identifier null when creating a GLN participant', function () {
    $request = new CreateParticipantRequest(new CreateParticipantData(
        companyId: '0101knwp96k3ggvkra831yrd74zh',
        scheme: IdentifierScheme::GLN,
        channels: [Channel::NemHandel, Channel::Peppol],
    ));

    expect($request->body()->all())->toBe([
        'company_id' => '0101knwp96k3ggvkra831yrd74zh',
        'scheme' => 'GLN',
        'identifier' => null,
        'channels' => ['NemHandel', 'Peppol'],
    ]);
});

it('can update a participant', function () {
    $mockClient = new MockClient([
        UpdateParticipantRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participant.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $participant = $connector->participants()->update(
        '0101knwp96k3ggvkra831yrd77ghi',
        [Channel::NemHandel],
    );

    expect($participant)->toBeInstanceOf(ParticipantData::class);
    $mockClient->assertSent(UpdateParticipantRequest::class);
});

it('serializes update participant body from the api schema', function () {
    $request = new UpdateParticipantRequest('0101knwp96k3ggvkra831yrd77ghi', [Channel::NemHandel, Channel::Peppol]);

    expect($request->resolveEndpoint())->toBe('/participants/0101knwp96k3ggvkra831yrd77ghi');
    expect($request->body()->all())->toBe(['channels' => ['NemHandel', 'Peppol']]);
});

it('can delete a participant', function () {
    $mockClient = new MockClient([
        DeleteParticipantRequest::class => MockResponse::make(status: 204),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $response = $connector->participants()->delete('0101knwp96k3ggvkra831yrd77ghi');

    expect($response->status())->toBe(204);
    $mockClient->assertSent(DeleteParticipantRequest::class);
});
