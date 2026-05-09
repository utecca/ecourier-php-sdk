<?php

declare(strict_types=1);

use Ecourier\Sdk\Data\ParticipantData;
use Ecourier\Sdk\EcourierConnector;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\IdentifierScheme;
use Ecourier\Sdk\Requests\Participants\GetParticipantRequest;
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
    expect($participant->id)->toBe('part_01ghi');
    expect($participant->name)->toBe('Beta Ltd');
    expect($participant->scheme)->toBe(IdentifierScheme::GLN);
    expect($participant->endpoint)->toBe('5790000123456');
    expect($participant->documentTypes)->toHaveCount(2);
});

it('builds the correct lookup endpoint', function () {
    $request = new GetParticipantRequest(
        channel: Channel::NemHandel,
        scheme: IdentifierScheme::DK_CVR,
        participantId: '12345678',
    );

    expect($request->resolveEndpoint())->toBe('/lookup/NemHandel/DK:CVR/12345678');
});
