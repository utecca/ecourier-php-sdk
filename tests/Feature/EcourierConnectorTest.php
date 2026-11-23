<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Ecourier\EcourierConnector;
use Ecourier\Requests\Participants\GetParticipantRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fakeParticipantMockClient(): MockClient
{
    return new MockClient([
        GetParticipantRequest::class => MockResponse::make(
            body: file_get_contents(__DIR__ . '/../Fixtures/participant.json'),
            status: 200,
            headers: ['Content-Type' => 'application/json'],
        ),
    ]);
}

it('authenticates with the api key as a bearer token', function () {
    $mockClient = fakeParticipantMockClient();

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $response = $connector->participants()->get('0101knwp96k3ggvkra831yrd77ghi');

    expect($response->getPendingRequest()->headers()->get('Authorization'))->toBe('Bearer pk_test_fake');
});

it('keeps the api key out of dumps of the connector', function () {
    $connector = new EcourierConnector(apiKey: 'pk_test_secret');
    $connector->withMockClient(fakeParticipantMockClient());

    expect(print_r($connector, true))->not->toContain('pk_test_secret')
        ->and(var_export($connector, true))->not->toContain('pk_test_secret')
        // Symfony's dump() and Laravel's dd() read the properties like this, and ignore __debugInfo().
        ->and(in_array('pk_test_secret', (array) $connector, true))->toBeFalse();
});

it('cannot be serialized, as that would write out the api key', function () {
    serialize(new EcourierConnector(apiKey: 'pk_test_secret'));
})->throws(Exception::class, "Serialization of 'SensitiveParameterValue' is not allowed");

it('sends a descriptive User-Agent header', function () {
    $mockClient = fakeParticipantMockClient();

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    $connector->participants()->get('0101knwp96k3ggvkra831yrd77ghi');

    $mockClient->assertSent(function (GetParticipantRequest $request, $response): bool {
        $userAgent = $response->getPendingRequest()->headers()->get('User-Agent');

        expect($userAgent)->toStartWith('ecourier-php-sdk/');
        expect($userAgent)->toContain('PHP ' . PHP_VERSION);
        expect($userAgent)->toContain('saloon/' . InstalledVersions::getPrettyVersion('saloonphp/saloon'));
        expect($userAgent)->not->toContain('ecourier-laravel/');

        return true;
    });
});

it('appends the ecourier-laravel version to the User-Agent when it is installed', function () {
    $property = new ReflectionProperty(InstalledVersions::class, 'installed');
    $property->setAccessible(true);
    $original = $property->getValue();

    $faked = $original;
    $faked['versions']['ecourier/ecourier-laravel'] = [
        'pretty_version' => '2.1.0',
        'version' => '2.1.0.0',
        'reference' => null,
        'type' => 'library',
        'install_path' => __DIR__,
        'aliases' => [],
        'dev_requirement' => false,
    ];
    InstalledVersions::reload($faked);

    try {
        $mockClient = fakeParticipantMockClient();

        $connector = new EcourierConnector(apiKey: 'pk_test_fake');
        $connector->withMockClient($mockClient);

        $connector->participants()->get('0101knwp96k3ggvkra831yrd77ghi');

        $mockClient->assertSent(function (GetParticipantRequest $request, $response): bool {
            $userAgent = $response->getPendingRequest()->headers()->get('User-Agent');

            expect($userAgent)->toContain('ecourier-laravel/2.1.0');

            return true;
        });
    } finally {
        InstalledVersions::reload($original);
    }
});
