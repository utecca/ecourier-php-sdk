<?php

declare(strict_types=1);

use Ecourier\Data\ParticipantData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;
use Ecourier\Requests\Participants\GetParticipantRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can look up a participant', function () {
    $mockClient = new MockClient([
        GetParticipantRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participant.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $participant = $connector->participants()->find(
        channel: Channel::Peppol,
        scheme: IdentifierScheme::GLN,
        participantId: '5790000123456',
    );

    expect($participant)->toBeInstanceOf(ParticipantData::class);
    expect($participant->channel)->toBe(Channel::Peppol);
    expect($participant->mode)->toBe(Mode::Live);
    expect($participant->entityName)->toBe('GLN Denmark');
    expect($participant->orgNo)->toBe('9999796418186');
});

it('maps the lookup response from the api schema', function () {
    $participant = ParticipantData::fromArray([
        'channel' => 'Peppol',
        'mode' => 'Live',
        'entityName' => 'GLN Denmark',
        'country' => 'DK',
        'registrationDate' => '2024-01-01',
        'orgNo' => '9999796418186',
        'registryUrl' => 'https://directory.peppol.eu/public/locale-en_US/menuitem-search',
    ]);

    expect($participant->channel)->toBe(Channel::Peppol);
    expect($participant->mode)->toBe(Mode::Live);
    expect($participant->entityName)->toBe('GLN Denmark');
    expect($participant->orgNo)->toBe('9999796418186');
});

it('builds the correct lookup endpoint', function () {
    $request = new GetParticipantRequest(
        channel: Channel::NemHandel,
        scheme: IdentifierScheme::DK_CVR,
        participantId: '12345678',
    );

    expect($request->resolveEndpoint())->toBe('/lookup/NemHandel/DK:CVR/12345678');
});
