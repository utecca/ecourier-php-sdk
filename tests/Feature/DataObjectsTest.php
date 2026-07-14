<?php

declare(strict_types=1);

use Ecourier\Data\AddressData;
use Ecourier\Data\CompanyData;
use Ecourier\Data\DocumentData;
use Ecourier\Data\Invoice\ParticipantIdentifier;
use Ecourier\Data\ParticipantData;
use Ecourier\Data\ParticipantLookupData;
use Ecourier\Data\PartyData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

// --- AddressData ---

it('serializes AddressData to array with all keys', function () {
    $address = new AddressData(
        street: 'Hovedgade 1',
        city: 'Copenhagen',
        zip: '1000',
        country: 'DK',
    );

    expect($address->toArray())->toBe([
        'street' => 'Hovedgade 1',
        'city' => 'Copenhagen',
        'zip' => '1000',
        'country' => 'DK',
    ]);
});

it('includes null values in AddressData toArray', function () {
    $address = new AddressData();

    $result = $address->toArray();

    expect($result)->toHaveKeys(['street', 'city', 'zip', 'country']);
    expect($result['street'])->toBeNull();
    expect($result['city'])->toBeNull();
});

it('roundtrips AddressData through fromArray and toArray', function () {
    $data = ['street' => 'Vesterbrogade 5', 'city' => 'Aarhus', 'zip' => '8000', 'country' => 'DK'];

    expect(AddressData::fromArray($data)->toArray())->toBe($data);
});

// --- PartyData ---

it('serializes PartyData to array with all keys', function () {
    $party = new PartyData(
        id: 'comp_01abc',
        name: 'Acme Corporation',
        cvr: '12345678',
        vat: 'DK12345678',
        country: 'DK',
        email: 'billing@acme.com',
    );

    expect($party->toArray())->toBe([
        'id' => 'comp_01abc',
        'name' => 'Acme Corporation',
        'cvr' => '12345678',
        'vat' => 'DK12345678',
        'country' => 'DK',
        'email' => 'billing@acme.com',
    ]);
});

it('includes null values in PartyData toArray', function () {
    $party = new PartyData(id: 'comp_01', name: 'Test');

    $result = $party->toArray();

    expect($result)->toHaveKeys(['id', 'name', 'cvr', 'vat', 'country', 'email']);
    expect($result['cvr'])->toBeNull();
    expect($result['vat'])->toBeNull();
});

it('roundtrips PartyData through fromArray and toArray', function () {
    $data = [
        'id' => 'comp_01abc',
        'name' => 'Acme Corporation',
        'cvr' => '12345678',
        'vat' => 'DK12345678',
        'country' => 'DK',
        'email' => 'billing@acme.com',
    ];

    expect(PartyData::fromArray($data)->toArray())->toBe($data);
});

// --- DocumentData ---

it('serializes DocumentData to array with all keys', function () {
    $document = new DocumentData(
        id: 'doc_01xyz',
        status: DocumentStatus::Delivered,
        channel: Channel::NemHandel,
        mode: Mode::Live,
        direction: Direction::Send,
        type: DocumentType::Invoice,
        submissionFormat: SubmissionFormat::JSON,
        sender: new ParticipantIdentifier(IdentifierScheme::DK_CVR, '12345678'),
        recipient: new ParticipantIdentifier(IdentifierScheme::GLN, '5790000123456'),
        latestE2eMessageUuid: 'ddc3b3ef-cbd4-4630-9d65-896b3e1abc61',
        latestE2eTransmissionId: 'trans_01def',
        createdAt: new DateTimeImmutable('2024-06-01T10:00:00Z'),
    );

    $result = $document->toArray();

    expect($result['id'])->toBe('doc_01xyz');
    expect($result['status'])->toBe('Delivered');
    expect($result['channel'])->toBe('NemHandel');
    expect($result['mode'])->toBe('Live');
    expect($result['direction'])->toBe('Send');
    expect($result['type'])->toBe('Invoice');
    expect($result['submission_format'])->toBe('JSON');
    expect($result['sender']['scheme'])->toBe('DK:CVR');
    expect($result['recipient']['id'])->toBe('5790000123456');
    expect($result['latest_e2e_message_uuid'])->toBe('ddc3b3ef-cbd4-4630-9d65-896b3e1abc61');
    expect($result['latest_e2e_transmission_id'])->toBe('trans_01def');
    expect($result['created_at'])->toBe('2024-06-01T10:00:00Z');
});

it('includes null values in DocumentData toArray', function () {
    $document = new DocumentData(
        id: 'doc_01xyz',
        status: DocumentStatus::Pending,
        channel: Channel::Peppol,
        mode: null,
        direction: Direction::Receive,
        type: DocumentType::Invoice,
    );

    $result = $document->toArray();

    expect($result)->toHaveKeys([
        'id', 'status', 'channel', 'mode', 'direction', 'type', 'submission_format',
        'sender', 'recipient', 'latest_e2e_message_uuid', 'latest_e2e_transmission_id', 'company', 'created_at',
    ]);
    expect($result['mode'])->toBeNull();
    expect($result['sender'])->toBeNull();
    expect($result['company'])->toBeNull();
});

it('serializes DocumentData sender and recipient via ParticipantIdentifier toArray', function () {
    $document = DocumentData::fromArray(
        json_decode(file_get_contents(__DIR__ . '/../Fixtures/document.json'), true),
    );

    $result = $document->toArray();

    expect($result['sender'])->toBeArray();
    expect($result['sender']['scheme'])->toBe('DK:CVR');
    expect($result['recipient'])->toBeArray();
    expect($result['recipient']['id'])->toBe('5790000123456');
    expect($result['company']['name'])->toBe('Acme Danmark A/S');
});

it('roundtrips DocumentData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/document.json'), true);

    $result = DocumentData::fromArray($fixture)->toArray();

    expect($result['id'])->toBe($fixture['id']);
    expect($result['status'])->toBe($fixture['status']);
    expect($result['channel'])->toBe($fixture['channel']);
    expect($result['mode'])->toBe($fixture['mode']);
    expect($result['direction'])->toBe($fixture['direction']);
    expect($result['type'])->toBe($fixture['type']);
    expect($result['submission_format'])->toBe($fixture['submission_format']);
    expect($result['latest_e2e_message_uuid'])->toBe($fixture['latest_e2e_message_uuid']);
    expect($result['latest_e2e_transmission_id'])->toBe($fixture['latest_e2e_transmission_id']);
});

// --- CompanyData ---

it('serializes CompanyData to array with all keys', function () {
    $company = new CompanyData(
        id: '0101knwp96k3ggvkra831yrd74zh',
        name: 'Acme Danmark A/S',
        mode: Mode::Live,
        companyNo: '12345678',
        createdAt: new DateTimeImmutable('2026-04-11T12:34:56+00:00'),
        updatedAt: new DateTimeImmutable('2026-05-21T09:15:30+00:00'),
        parentId: null,
        children: ['0101knwp96k3ggvkra831yrd76def'],
        country: 'DK',
        authorisation: null,
        participants: [],
    );

    $result = $company->toArray();

    expect($result['id'])->toBe('0101knwp96k3ggvkra831yrd74zh');
    expect($result['name'])->toBe('Acme Danmark A/S');
    expect($result['mode'])->toBe('Live');
    expect($result['company_no'])->toBe('12345678');
    expect($result['children'])->toHaveCount(1);
});

it('serializes CompanyData nested authorisation and participants', function () {
    $company = CompanyData::fromArray(
        json_decode(file_get_contents(__DIR__ . '/../Fixtures/company.json'), true),
    );

    $result = $company->toArray();

    expect($result['authorisation'])->toBeArray();
    expect($result['authorisation']['signer']['first_name'])->toBe('Pernille');
    expect($result['participants'][0]['full_identifier'])->toBe('0088:5790000435944');
});

it('roundtrips CompanyData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/company.json'), true);

    $result = CompanyData::fromArray($fixture)->toArray();

    expect($result['id'])->toBe($fixture['id']);
    expect($result['name'])->toBe($fixture['name']);
    expect($result['mode'])->toBe($fixture['mode']);
    expect($result['company_no'])->toBe($fixture['company_no']);
    expect($result['country'])->toBe($fixture['country']);
    expect($result['children'])->toBe($fixture['children']);
});

// --- ParticipantLookupData ---

it('serializes ParticipantLookupData to array with all keys', function () {
    $participant = new ParticipantLookupData(
        channel: Channel::Peppol,
        mode: Mode::Live,
        entityName: 'GLN Denmark',
        country: 'DK',
        registrationDate: '2024-01-01',
        orgNo: '9999796418186',
        registryUrl: 'https://directory.peppol.eu/public/locale-en_US/menuitem-search',
    );

    $result = $participant->toArray();

    expect($result['channel'])->toBe('Peppol');
    expect($result['mode'])->toBe('Live');
    expect($result['entityName'])->toBe('GLN Denmark');
    expect($result['orgNo'])->toBe('9999796418186');
});

it('roundtrips ParticipantLookupData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/lookup-participant.json'), true);

    $result = ParticipantLookupData::fromArray($fixture)->toArray();

    expect($result['channel'])->toBe($fixture['channel']);
    expect($result['mode'])->toBe($fixture['mode']);
    expect($result['entityName'])->toBe($fixture['entityName']);
    expect($result['country'])->toBe($fixture['country']);
    expect($result['orgNo'])->toBe($fixture['orgNo']);
});

// --- ParticipantData ---

it('roundtrips ParticipantData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/participant.json'), true);

    $result = ParticipantData::fromArray($fixture)->toArray();

    expect($result['id'])->toBe($fixture['id']);
    expect($result['company'])->toBe($fixture['company']);
    expect($result['mode'])->toBe($fixture['mode']);
    expect($result['scheme'])->toBe($fixture['scheme']);
    expect($result['identifier'])->toBe($fixture['identifier']);
    expect($result['full_identifier'])->toBe($fixture['full_identifier']);
    expect($result['channels'])->toBe($fixture['channels']);
});
