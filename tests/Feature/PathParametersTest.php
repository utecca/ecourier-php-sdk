<?php

declare(strict_types=1);

use Ecourier\Data\Requests\SubmitAuthorisationPdfData;
use Ecourier\Data\Requests\UpdateCompanyData;
use Ecourier\Data\Requests\UpdateParticipantData;
use Ecourier\Data\Responses\CompanyAuthorisationSignerData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

/**
 * Every public method that puts a caller-supplied value into the request path.
 *
 * @return array<string, array{Closure(EcourierConnector, string): mixed, string}>
 */
function pathParameterCalls(): array
{
    return [
        'companies get' => [fn(EcourierConnector $c, string $id) => $c->companies()->get($id), 'company ID'],
        'companies find' => [fn(EcourierConnector $c, string $id) => $c->companies()->find($id), 'company ID'],
        'companies update' => [fn(EcourierConnector $c, string $id) => $c->companies()->update($id, new UpdateCompanyData(name: 'Acme')), 'company ID'],
        'companies submitAuthorisationPdf' => [
            fn(EcourierConnector $c, string $id) => $c->companies()->submitAuthorisationPdf($id, new SubmitAuthorisationPdfData(
                pdf: '%PDF-1.4',
                signer: new CompanyAuthorisationSignerData('Ada', 'Lovelace', 'CEO'),
            )),
            'company ID',
        ],
        'companies delete' => [fn(EcourierConnector $c, string $id) => $c->companies()->delete($id), 'company ID'],
        'documents get' => [fn(EcourierConnector $c, string $id) => $c->documents()->get($id), 'document ID'],
        'documents find' => [fn(EcourierConnector $c, string $id) => $c->documents()->find($id), 'document ID'],
        'documents contentAsXml' => [fn(EcourierConnector $c, string $id) => $c->documents()->contentAsXml($id), 'document ID'],
        'documents renderAsHtml' => [fn(EcourierConnector $c, string $id) => $c->documents()->renderAsHtml($id), 'document ID'],
        'documents renderAsPdf' => [fn(EcourierConnector $c, string $id) => $c->documents()->renderAsPdf($id), 'document ID'],
        'documents markDelivered' => [fn(EcourierConnector $c, string $id) => $c->documents()->markDelivered($id), 'document ID'],
        'participants get' => [fn(EcourierConnector $c, string $id) => $c->participants()->get($id), 'participant ID'],
        'participants find' => [fn(EcourierConnector $c, string $id) => $c->participants()->find($id), 'participant ID'],
        'participants update' => [fn(EcourierConnector $c, string $id) => $c->participants()->update($id, new UpdateParticipantData(channels: [Channel::Peppol])), 'participant ID'],
        'participants delete' => [fn(EcourierConnector $c, string $id) => $c->participants()->delete($id), 'participant ID'],
        'lookup get' => [fn(EcourierConnector $c, string $id) => $c->lookup()->get(Channel::Peppol, IdentifierScheme::GLN, $id), 'participant ID'],
        'lookup findParticipant' => [fn(EcourierConnector $c, string $id) => $c->lookup()->findParticipant(Channel::Peppol, IdentifierScheme::GLN, $id), 'participant ID'],
    ];
}

/**
 * @return array{EcourierConnector, MockClient}
 */
function connectorRecordingRequests(): array
{
    $mockClient = new MockClient([
        '*' => MockResponse::make(body: '{}', status: 200, headers: ['Content-Type' => 'application/json']),
    ]);

    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient($mockClient);

    return [$connector, $mockClient];
}

function sentPath(MockClient $mockClient): string
{
    $response = $mockClient->getLastResponse();

    expect($response)->toBeInstanceOf(Response::class);

    $uri = $response->getPsrRequest()->getUri();

    return $uri->getPath() . ($uri->getQuery() !== '' ? '?' . $uri->getQuery() : '');
}

it('rejects an empty or dot-segment path parameter before sending a request', function (Closure $call, string $name, string $value, string $message) {
    [$connector, $mockClient] = connectorRecordingRequests();

    expect(fn() => $call($connector, $value))
        ->toThrow(InvalidArgumentException::class, sprintf($message, $name));

    $mockClient->assertNothingSent();
})->with(pathParameterCalls())->with([
    'empty' => ['', 'The %s must not be empty.'],
    'single dot' => ['.', 'The %s must not be ".".'],
    'double dot' => ['..', 'The %s must not be "..".'],
]);

it('keeps a path parameter inside its own path segment', function (Closure $call) {
    [$connector, $mockClient] = connectorRecordingRequests();

    try {
        $call($connector, '../../../../documents/01abc?filter[x]=y#fragment');
    } catch (UnexpectedValueException) {
        // The mocked "{}" body cannot be parsed into a data object. Only the request matters here.
    }

    expect(sentPath($mockClient))
        ->toContain('..%2F..%2F..%2F..%2Fdocuments%2F01abc%3Ffilter%5Bx%5D%3Dy%23fragment')
        ->not->toContain('/../')
        ->not->toContain('?');
})->with(pathParameterCalls());

it('sends the encoded lookup identifier to the lookup endpoint', function () {
    [$connector, $mockClient] = connectorRecordingRequests();

    $connector->lookup()->get(Channel::NemHandel, IdentifierScheme::DK_CVR, '../../../../documents/01abc');

    expect(sentPath($mockClient))
        ->toBe('/v1/lookup/participants/NemHandel/DK:CVR/..%2F..%2F..%2F..%2Fdocuments%2F01abc');
});

it('sends an encoded id to the endpoint of the method that was called', function () {
    [$connector, $mockClient] = connectorRecordingRequests();

    $connector->participants()->delete('../companies/0101knwp96k3ggvkra831yrd74zh');

    expect(sentPath($mockClient))->toBe('/v1/participants/..%2Fcompanies%2F0101knwp96k3ggvkra831yrd74zh');
});

it('leaves ordinary ids and identifiers unchanged', function (Closure $call, string $value, string $expected) {
    [$connector, $mockClient] = connectorRecordingRequests();

    $call($connector, $value);

    expect(sentPath($mockClient))->toBe($expected);
})->with([
    'ulid' => [
        fn(EcourierConnector $c, string $id) => $c->documents()->get($id),
        '01kmkdaf55vrrecfy70180tpr6',
        '/v1/documents/01kmkdaf55vrrecfy70180tpr6',
    ],
    'ulid with a suffix' => [
        fn(EcourierConnector $c, string $id) => $c->documents()->contentAsXml($id),
        '01kmkdaf55vrrecfy70180tpr6',
        '/v1/documents/01kmkdaf55vrrecfy70180tpr6/xml',
    ],
    'numeric identifier' => [
        fn(EcourierConnector $c, string $id) => $c->lookup()->get(Channel::Peppol, IdentifierScheme::GLN, $id),
        '5790000123456',
        '/v1/lookup/participants/Peppol/GLN/5790000123456',
    ],
    'identifier with unreserved characters' => [
        fn(EcourierConnector $c, string $id) => $c->lookup()->get(Channel::Peppol, IdentifierScheme::GLN, $id),
        'AB-12.34_5~6',
        '/v1/lookup/participants/Peppol/GLN/AB-12.34_5~6',
    ],
    'identifier with a space' => [
        fn(EcourierConnector $c, string $id) => $c->lookup()->get(Channel::Peppol, IdentifierScheme::GLN, $id),
        '12 34',
        '/v1/lookup/participants/Peppol/GLN/12%2034',
    ],
]);
