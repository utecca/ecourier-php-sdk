<?php

declare(strict_types=1);

use Ecourier\Data\Responses\CompanyData;
use Ecourier\Data\Responses\DocumentData;
use Ecourier\Data\Responses\DocumentParticipantData;
use Ecourier\Data\Responses\DocumentUblData;
use Ecourier\Data\Responses\DocumentValidationData;
use Ecourier\Data\Responses\ParticipantData;
use Ecourier\Data\Responses\ParticipantLookupData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

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
        sender: new DocumentParticipantData(IdentifierScheme::DK_CVR, '12345678', '0184', '0184:12345678'),
        recipient: new DocumentParticipantData(IdentifierScheme::GLN, '5790000123456', '0088', '0088:5790000123456'),
        webUrl: 'https://app.ecourier.io/live/acme-danmark/documents/doc_01xyz',
        createdAt: new DateTimeImmutable('2024-06-01T10:00:00Z'),
        ubl: new DocumentUblData('INV-2024-001', null, null, null),
        validation: new DocumentValidationData(errors: [], warnings: []),
        attempts: [],
        latestE2eMessageUuid: 'ddc3b3ef-cbd4-4630-9d65-896b3e1abc61',
        latestE2eTransmissionId: 'trans_01def',
        company: null,
        transmittedAt: null,
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
    expect($result['recipient']['identifier'])->toBe('5790000123456');
    expect($result['latest_e2e_message_uuid'])->toBe('ddc3b3ef-cbd4-4630-9d65-896b3e1abc61');
    expect($result['latest_e2e_transmission_id'])->toBe('trans_01def');
    expect($result['created_at'])->toBe('2024-06-01T10:00:00Z');
});

it('includes null values in DocumentData toArray', function () {
    $document = new DocumentData(
        id: 'doc_01xyz',
        status: DocumentStatus::Pending,
        channel: Channel::Peppol,
        mode: Mode::Test,
        direction: Direction::Receive,
        type: DocumentType::Invoice,
        submissionFormat: SubmissionFormat::XML,
        sender: new DocumentParticipantData(IdentifierScheme::DK_CVR, '12345678', '0184', '0184:12345678'),
        recipient: new DocumentParticipantData(IdentifierScheme::GLN, '5790000123456', '0088', '0088:5790000123456'),
        webUrl: 'https://app.ecourier.io/test/acme-danmark/documents/doc_01xyz',
        createdAt: new DateTimeImmutable('2024-06-01T10:00:00Z'),
        ubl: new DocumentUblData(null, null, null, null),
        validation: new DocumentValidationData(errors: [], warnings: []),
        attempts: [],
        latestE2eMessageUuid: null,
        latestE2eTransmissionId: null,
        company: null,
        transmittedAt: null,
    );

    $result = $document->toArray();

    expect($result)->toHaveKeys([
        'id', 'status', 'channel', 'mode', 'direction', 'type', 'submission_format',
        'sender', 'recipient', 'latest_e2e_message_uuid', 'latest_e2e_transmission_id', 'company',
        'dashboard_url', 'created_at', 'transmitted_at', 'ubl', 'validation',
    ]);
    expect($result['latest_e2e_message_uuid'])->toBeNull();
    expect($result['company'])->toBeNull();
    expect($result['transmitted_at'])->toBeNull();
});

it('serializes DocumentData sender and recipient via ParticipantIdentifier toArray', function () {
    $document = DocumentData::fromArray(
        json_decode(file_get_contents(__DIR__ . '/../Fixtures/document.json'), true),
    );

    $result = $document->toArray();

    expect($result['sender'])->toBeArray();
    expect($result['sender']['scheme'])->toBe('DK:CVR');
    expect($result['recipient'])->toBeArray();
    expect($result['recipient']['identifier'])->toBe('5790000123456');
    expect($result['company']['name'])->toBe('Acme Danmark A/S');
    expect($result['ubl']['id'])->toBe('INV-2024-001');
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
    expect($result['dashboard_url'])->toBe($fixture['dashboard_url']);
    expect($result['created_at'])->toBe($fixture['created_at']);
    expect($result['transmitted_at'])->toBe($fixture['transmitted_at']);
    expect($result['ubl']['profile_id'])->toBe($fixture['ubl']['profile_id']);
    expect($result['validation'])->toBe($fixture['validation']);
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
        country: 'DK',
        authorisation: null,
        participants: [],
    );

    $result = $company->toArray();

    expect($result['id'])->toBe('0101knwp96k3ggvkra831yrd74zh');
    expect($result['name'])->toBe('Acme Danmark A/S');
    expect($result['mode'])->toBe('Live');
    expect($result['company_no'])->toBe('12345678');
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
