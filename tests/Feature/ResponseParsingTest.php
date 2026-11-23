<?php

declare(strict_types=1);

use Ecourier\Data\Responses\CompanyData;
use Ecourier\Data\Responses\CompanyListItemData;
use Ecourier\Data\Responses\DocumentData;
use Ecourier\Data\Responses\DocumentListItemData;
use Ecourier\Data\Responses\DocumentValidationData;
use Ecourier\Data\Responses\ParticipantData;
use Ecourier\EcourierConnector;
use Ecourier\Enums\Channel;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Exceptions\InvalidResponseFieldException;
use Ecourier\Exceptions\MissingResponseFieldException;
use Ecourier\Exceptions\UnexpectedResponseException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function fixtureArray(string $name): array
{
    return json_decode((string) file_get_contents(__DIR__ . "/../Fixtures/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);
}

// --- Missing fields ---

it('throws a named exception when a required field is missing', function () {
    $data = fixtureArray('document');
    unset($data['web_url']);

    DocumentData::fromArray($data);
})->throws(
    MissingResponseFieldException::class,
    'The eCourier response is missing the required field "web_url" for Ecourier\Data\Responses\DocumentData.',
);

it('treats a null required field as missing', function () {
    $data = fixtureArray('document');
    $data['created_at'] = null;

    DocumentData::fromArray($data);
})->throws(MissingResponseFieldException::class);

it('names the nested data object and field that are missing', function () {
    $data = fixtureArray('document');
    unset($data['sender']['identifier']);

    try {
        DocumentData::fromArray($data);
    } catch (MissingResponseFieldException $exception) {
        expect($exception->dataClass)->toBe(Ecourier\Data\Responses\DocumentParticipantData::class)
            ->and($exception->field)->toBe('identifier');

        return;
    }

    test()->fail('Expected a MissingResponseFieldException.');
});

it('requires mode on every resource that has it', function (string $class, string $fixture) {
    $data = fixtureArray($fixture);
    unset($data['mode']);

    $class::fromArray($data);
})->throws(MissingResponseFieldException::class, 'the required field "mode"')->with([
    'document' => [DocumentData::class, 'document'],
    'company' => [CompanyData::class, 'company'],
    'participant' => [ParticipantData::class, 'participant'],
]);

it('defaults missing nullable fields to null and missing lists to empty', function () {
    $data = fixtureArray('document');
    unset($data['latest_e2e_message_uuid'], $data['transmitted_at'], $data['company'], $data['attempts']);

    $document = DocumentData::fromArray($data);

    expect($document->latestE2eMessageUuid)->toBeNull()
        ->and($document->transmittedAt)->toBeNull()
        ->and($document->company)->toBeNull()
        ->and($document->attempts)->toBe([]);
});

it('defaults missing participant channels to an empty list', function () {
    $data = fixtureArray('participant');
    unset($data['channels']);

    expect(ParticipantData::fromArray($data)->channels)->toBe([]);
});

it('defaults missing validation issue lists to empty', function () {
    $validation = DocumentValidationData::fromArray([]);

    expect($validation->errors)->toBe([])
        ->and($validation->warnings)->toBe([]);
});

// --- Wrong-typed fields ---

it('throws a named exception when a field has the wrong type', function () {
    $data = fixtureArray('document');
    $data['id'] = 123;

    DocumentData::fromArray($data);
})->throws(
    InvalidResponseFieldException::class,
    'The eCourier response field "id" for Ecourier\Data\Responses\DocumentData must be a string, int given.',
);

it('names the nested data object and field that has the wrong type', function () {
    $data = fixtureArray('document');
    $data['sender']['identifier'] = ['12345678'];

    try {
        DocumentData::fromArray($data);
    } catch (InvalidResponseFieldException $exception) {
        expect($exception->dataClass)->toBe(Ecourier\Data\Responses\DocumentParticipantData::class)
            ->and($exception->field)->toBe('identifier')
            ->and($exception)->toBeInstanceOf(UnexpectedResponseException::class);

        return;
    }

    test()->fail('Expected an InvalidResponseFieldException.');
});

it('rejects a wrong-typed field instead of raising a TypeError', function (string $fixture, string $class, callable $break, string $field) {
    $data = $break(fixtureArray($fixture));

    try {
        $class::fromArray($data);
    } catch (InvalidResponseFieldException $exception) {
        expect($exception->field)->toBe($field);

        return;
    }

    test()->fail('Expected an InvalidResponseFieldException.');
})->with([
    'optional string' => ['document', DocumentData::class, fn(array $d) => ['latest_e2e_message_uuid' => 5] + $d, 'latest_e2e_message_uuid'],
    'nested object' => ['document', DocumentData::class, fn(array $d) => ['sender' => 'acme'] + $d, 'sender'],
    'optional nested object' => ['document', DocumentData::class, fn(array $d) => ['company' => 'acme'] + $d, 'company'],
    'list of objects' => ['company', CompanyData::class, fn(array $d) => ['participants' => ['acme']] + $d, 'participants'],
    'date' => ['document', DocumentData::class, fn(array $d) => ['created_at' => 1717236360] + $d, 'created_at'],
    'malformed date' => ['document', DocumentData::class, fn(array $d) => ['created_at' => 'not a date'] + $d, 'created_at'],
    'optional date' => ['document', DocumentData::class, fn(array $d) => ['transmitted_at' => 1717236360] + $d, 'transmitted_at'],
    'strict enum' => ['document', DocumentData::class, fn(array $d) => ['mode' => 1] + $d, 'mode'],
    'strict enum in a list' => ['participant', ParticipantData::class, fn(array $d) => ['channels' => [1]] + $d, 'channels'],
]);

it('maps a wrong-typed value for a lenient enum to null', function () {
    $data = fixtureArray('document');
    $data['status'] = 5;

    expect(DocumentData::fromArray($data)->status)->toBeNull();
});

// --- Response bodies ---

it('throws a named exception when a successful response is not a JSON object', function (string $body) {
    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient(new MockClient([MockResponse::make(body: $body, status: 200)]));

    $connector->companies()->find('comp_01abc');
})->throws(UnexpectedResponseException::class, 'The eCourier response body is not a JSON object.')->with([
    'null' => ['null'],
    'a string' => ['"OK"'],
    'a number' => ['123'],
    'html' => ['<html><body>OK</body></html>'],
]);

it('throws a named exception when a page of results is not a list of objects', function (string $body) {
    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient(new MockClient([MockResponse::make(body: $body, status: 200)]));

    iterator_to_array($connector->companies()->list()->items());
})->throws(UnexpectedResponseException::class)->with([
    'null body' => ['null'],
    'data is a string' => ['{"data": "nope"}'],
    'an item is a string' => ['{"data": ["nope"]}'],
]);

it('treats a page without data as empty', function () {
    $connector = new EcourierConnector(apiKey: 'pk_test_fake');
    $connector->withMockClient(new MockClient([MockResponse::make(body: '{"meta": "nope"}', status: 200)]));

    expect(iterator_to_array($connector->companies()->list()->items()))->toBe([]);
});

// --- Unknown enum values ---

it('maps an unknown enum value to null and still parses the rest', function () {
    $data = fixtureArray('document');
    $data['status'] = 'SomeFutureStatus';
    $data['sender']['scheme'] = 'XX:NEW';

    $document = DocumentData::fromArray($data);

    expect($document->status)->toBeNull()
        ->and($document->sender->scheme)->toBeNull()
        ->and($document->sender->identifier)->toBe('12345678')
        ->and($document->channel)->toBe(Channel::NemHandel);
});

it('keeps known enum values', function () {
    $document = DocumentData::fromArray(fixtureArray('document'));

    expect($document->status)->toBe(DocumentStatus::Delivered)
        ->and($document->recipient->scheme)->toBe(IdentifierScheme::GLN);
});

it('rejects an unknown channel in a list of channels', function () {
    $data = fixtureArray('participant');
    $data['channels'] = ['NemHandel', 'SomeFutureChannel'];

    ParticipantData::fromArray($data);
})->throws(ValueError::class);

it('rejects an unknown channel or direction', function (string $field) {
    $data = fixtureArray('document');
    $data[$field] = 'SomeFutureValue';

    DocumentData::fromArray($data);
})->throws(ValueError::class)->with(['channel', 'direction']);

it('requires channel and direction on documents', function (string $class, string $fixture, ?string $listKey, string $field) {
    $data = fixtureArray($fixture);
    $item = $listKey !== null ? $data[$listKey][0] : $data;
    unset($item[$field]);

    $class::fromArray($item);
})->throws(MissingResponseFieldException::class)->with([
    'document channel' => [DocumentData::class, 'document', null, 'channel'],
    'document direction' => [DocumentData::class, 'document', null, 'direction'],
    'document list item channel' => [DocumentListItemData::class, 'documents-paginated', 'data', 'channel'],
    'document list item direction' => [DocumentListItemData::class, 'documents-paginated', 'data', 'direction'],
]);

// --- Web URLs ---

it('reads web_url for every resource that links to the eCourier portal', function (string $class, string $fixture, ?string $listKey, string $expected) {
    $data = fixtureArray($fixture);
    $item = $listKey !== null ? $data[$listKey][0] : $data;

    $object = $class::fromArray($item);

    expect($object->webUrl)->toBe($expected);
})->with([
    'document' => [DocumentData::class, 'document', null, 'https://app.ecourier.io/live/acme-danmark/documents/01kmkdaf55vrrecfy70180tpr6'],
    'document list item' => [DocumentListItemData::class, 'documents-paginated', 'data', 'https://app.ecourier.io/live/acme-danmark/documents/01kmkdaf55vrrecfy70180tpr6'],
    'company' => [CompanyData::class, 'company', null, 'https://app.ecourier.io/live/acme-danmark/companies/0101knwp96k3ggvkra831yrd74zh'],
    'company list item' => [CompanyListItemData::class, 'companies-paginated', 'data', 'https://app.ecourier.io/live/acme-danmark/companies/0101knwp96k3ggvkra831yrd74zh'],
    'participant' => [ParticipantData::class, 'participant', null, 'https://app.ecourier.io/live/acme-danmark/companies/0101knwp96k3ggvkra831yrd74zh?tab=participants'],
]);
