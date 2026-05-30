<?php

declare(strict_types=1);

use Ecourier\Sdk\Data\AddressData;
use Ecourier\Sdk\Data\CompanyData;
use Ecourier\Sdk\Data\DocumentData;
use Ecourier\Sdk\Data\ParticipantData;
use Ecourier\Sdk\Data\PartyData;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\Currency;
use Ecourier\Sdk\Enums\Direction;
use Ecourier\Sdk\Enums\DocumentStatus;
use Ecourier\Sdk\Enums\DocumentType;
use Ecourier\Sdk\Enums\IdentifierScheme;

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
        direction: Direction::Send,
        type: DocumentType::Invoice,
        channel: Channel::NemHandel,
        reference: 'INV-2024-001',
        issueDate: new DateTimeImmutable('2024-06-01'),
        totalAmount: 1250.0,
        currency: Currency::DKK,
        createdAt: new DateTimeImmutable('2024-06-01T10:00:00Z'),
        updatedAt: new DateTimeImmutable('2024-06-01T10:05:00Z'),
        deliveredAt: new DateTimeImmutable('2024-06-01T10:05:00Z'),
    );

    $result = $document->toArray();

    expect($result['id'])->toBe('doc_01xyz');
    expect($result['status'])->toBe('Delivered');
    expect($result['direction'])->toBe('Send');
    expect($result['type'])->toBe('Invoice');
    expect($result['channel'])->toBe('NemHandel');
    expect($result['reference'])->toBe('INV-2024-001');
    expect($result['issue_date'])->toBe('2024-06-01');
    expect($result['total_amount'])->toBe(1250.0);
    expect($result['currency'])->toBe('DKK');
    expect($result['created_at'])->toBe('2024-06-01T10:00:00Z');
});

it('includes null values in DocumentData toArray', function () {
    $document = new DocumentData(
        id: 'doc_01xyz',
        status: DocumentStatus::Pending,
        direction: Direction::Receive,
    );

    $result = $document->toArray();

    expect($result)->toHaveKeys([
        'id', 'status', 'direction', 'type', 'channel', 'reference',
        'issue_date', 'total_amount', 'currency', 'sender', 'receiver',
        'created_at', 'updated_at', 'delivered_at', 'errors',
    ]);
    expect($result['type'])->toBeNull();
    expect($result['sender'])->toBeNull();
    expect($result['errors'])->toBeNull();
});

it('serializes DocumentData sender and receiver via PartyData toArray', function () {
    $document = DocumentData::fromArray(
        json_decode(file_get_contents(__DIR__ . '/../Fixtures/document.json'), true)
    );

    $result = $document->toArray();

    expect($result['sender'])->toBeArray();
    expect($result['sender']['name'])->toBe('Acme Corporation');
    expect($result['receiver'])->toBeArray();
    expect($result['receiver']['name'])->toBe('Beta Ltd');
});

it('roundtrips DocumentData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/document.json'), true);

    $result = DocumentData::fromArray($fixture)->toArray();

    expect($result['id'])->toBe($fixture['id']);
    expect($result['status'])->toBe($fixture['status']);
    expect($result['direction'])->toBe($fixture['direction']);
    expect($result['type'])->toBe($fixture['type']);
    expect($result['channel'])->toBe($fixture['channel']);
    expect($result['reference'])->toBe($fixture['reference']);
    expect($result['issue_date'])->toBe($fixture['issue_date']);
    expect($result['currency'])->toBe($fixture['currency']);
});

// --- CompanyData ---

it('serializes CompanyData to array with all keys', function () {
    $company = new CompanyData(
        id: 'comp_01abc',
        name: 'Acme Corporation',
        cvr: '12345678',
        vat: 'DK12345678',
        country: 'DK',
        email: 'billing@acme.com',
        phone: '+4512345678',
    );

    $result = $company->toArray();

    expect($result['id'])->toBe('comp_01abc');
    expect($result['name'])->toBe('Acme Corporation');
    expect($result['cvr'])->toBe('12345678');
    expect($result['address'])->toBeNull();
    expect($result['created_at'])->toBeNull();
});

it('includes null values in CompanyData toArray', function () {
    $company = new CompanyData(id: 'comp_01', name: 'Test');

    $result = $company->toArray();

    expect($result)->toHaveKeys([
        'id', 'name', 'cvr', 'vat', 'country', 'email', 'phone', 'address', 'created_at', 'updated_at',
    ]);
    expect($result['cvr'])->toBeNull();
    expect($result['address'])->toBeNull();
});

it('serializes CompanyData address via AddressData toArray', function () {
    $company = CompanyData::fromArray(
        json_decode(file_get_contents(__DIR__ . '/../Fixtures/company.json'), true)
    );

    $result = $company->toArray();

    expect($result['address'])->toBeArray();
    expect($result['address']['city'])->toBe('Copenhagen');
    expect($result['address']['zip'])->toBe('1000');
});

it('roundtrips CompanyData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/company.json'), true);

    $result = CompanyData::fromArray($fixture)->toArray();

    expect($result['id'])->toBe($fixture['id']);
    expect($result['name'])->toBe($fixture['name']);
    expect($result['cvr'])->toBe($fixture['cvr']);
    expect($result['vat'])->toBe($fixture['vat']);
    expect($result['country'])->toBe($fixture['country']);
    expect($result['address'])->toBe($fixture['address']);
});

// --- ParticipantData ---

it('serializes ParticipantData to array with all keys', function () {
    $participant = new ParticipantData(
        id: 'part_01ghi',
        name: 'Beta Ltd',
        scheme: IdentifierScheme::GLN,
        endpoint: '5790000123456',
        country: 'DK',
        documentTypes: ['urn:oasis:names:specification:ubl:schema:xsd:Invoice-2'],
    );

    $result = $participant->toArray();

    expect($result['id'])->toBe('part_01ghi');
    expect($result['name'])->toBe('Beta Ltd');
    expect($result['scheme'])->toBe('GLN');
    expect($result['endpoint'])->toBe('5790000123456');
    expect($result['document_types'])->toHaveCount(1);
});

it('includes null values in ParticipantData toArray', function () {
    $participant = new ParticipantData(id: 'part_01', name: 'Test');

    $result = $participant->toArray();

    expect($result)->toHaveKeys([
        'id', 'name', 'scheme', 'endpoint', 'country', 'document_types', 'created_at', 'updated_at',
    ]);
    expect($result['scheme'])->toBeNull();
    expect($result['document_types'])->toBeNull();
});

it('roundtrips ParticipantData through fromArray and toArray', function () {
    $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/participant.json'), true);

    $result = ParticipantData::fromArray($fixture)->toArray();

    expect($result['id'])->toBe($fixture['id']);
    expect($result['name'])->toBe($fixture['name']);
    expect($result['scheme'])->toBe($fixture['scheme']);
    expect($result['endpoint'])->toBe($fixture['endpoint']);
    expect($result['country'])->toBe($fixture['country']);
    expect($result['document_types'])->toBe($fixture['document_types']);
});

