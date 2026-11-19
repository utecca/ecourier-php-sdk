<?php

declare(strict_types=1);

use Ecourier\Data\Responses\CompanyData;
use Ecourier\Data\Responses\DocumentData;
use Ecourier\Data\Responses\ParticipantData;
use Ecourier\Data\Responses\ParticipantLookupData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentAttemptStatus;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

function dataObjectFixture(string $name): array
{
    return json_decode((string) file_get_contents(__DIR__ . "/../Fixtures/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);
}

// --- DocumentData ---

it('parses DocumentData from a full response', function () {
    $document = DocumentData::fromArray(dataObjectFixture('document'));

    expect($document->id)->toBe('01kmkdaf55vrrecfy70180tpr6')
        ->and($document->status)->toBe(DocumentStatus::Delivered)
        ->and($document->channel)->toBe(Channel::NemHandel)
        ->and($document->mode)->toBe(Mode::Live)
        ->and($document->direction)->toBe(Direction::Send)
        ->and($document->type)->toBe(DocumentType::Invoice)
        ->and($document->submissionFormat)->toBe(SubmissionFormat::JSON)
        ->and($document->sender->scheme)->toBe(IdentifierScheme::DK_CVR)
        ->and($document->recipient->identifier)->toBe('5790000123456')
        ->and($document->latestE2eMessageUuid)->toBe('ddc3b3ef-cbd4-4630-9d65-896b3e1abc61')
        ->and($document->latestE2eTransmissionId)->toBe('trans_01def')
        ->and($document->company?->name)->toBe('Acme Danmark A/S')
        ->and($document->webUrl)->toBe('https://app.ecourier.io/live/acme-danmark/documents/01kmkdaf55vrrecfy70180tpr6')
        ->and($document->createdAt)->toEqual(new DateTimeImmutable('2024-06-01T10:00:00Z'))
        ->and($document->transmittedAt)->toEqual(new DateTimeImmutable('2024-06-01T10:05:00Z'))
        ->and($document->ubl->id)->toBe('INV-2024-001')
        ->and($document->ubl->profileId)->toBe('urn:www.nesubl.eu:profiles:profile5:ver2.0')
        ->and($document->validation->hasErrors())->toBeFalse()
        ->and($document->validation->warnings[0]->code)->toBe('W-INV181')
        ->and($document->attempts)->toHaveCount(1)
        ->and($document->attempts[0]->status)->toBe(DocumentAttemptStatus::Success);
});

it('parses optional DocumentData fields as null', function () {
    $data = dataObjectFixture('document');
    unset($data['latest_e2e_message_uuid'], $data['latest_e2e_transmission_id'], $data['company'], $data['transmitted_at']);

    $document = DocumentData::fromArray($data);

    expect($document->latestE2eMessageUuid)->toBeNull()
        ->and($document->latestE2eTransmissionId)->toBeNull()
        ->and($document->company)->toBeNull()
        ->and($document->transmittedAt)->toBeNull();
});

// --- CompanyData ---

it('parses CompanyData with its authorisation and participants', function () {
    $company = CompanyData::fromArray(dataObjectFixture('company'));

    expect($company->id)->toBe('0101knwp96k3ggvkra831yrd74zh')
        ->and($company->name)->toBe('Acme Danmark A/S')
        ->and($company->mode)->toBe(Mode::Live)
        ->and($company->companyNo)->toBe('12345678')
        ->and($company->country)->toBe('DK')
        ->and($company->createdAt)->toEqual(new DateTimeImmutable('2026-04-11T12:34:56+00:00'))
        ->and($company->authorisation?->signer->firstName)->toBe('Pernille')
        ->and($company->participants[0]->fullIdentifier)->toBe('0088:5790000435944')
        ->and($company->participants[0]->channels)->toBe([Channel::NemHandel]);
});

// --- ParticipantLookupData ---

it('parses ParticipantLookupData', function () {
    $participant = ParticipantLookupData::fromArray(dataObjectFixture('lookup-participant'));

    expect($participant->channel)->toBe(Channel::Peppol)
        ->and($participant->mode)->toBe(Mode::Live)
        ->and($participant->entityName)->toBe('GLN Denmark')
        ->and($participant->country)->toBe('DK')
        ->and($participant->orgNo)->toBe('9999796418186');
});

// --- ParticipantData ---

it('parses ParticipantData', function () {
    $participant = ParticipantData::fromArray(dataObjectFixture('participant'));

    expect($participant->id)->toBe('0101knwp96k3ggvkra831yrd77ghi')
        ->and($participant->company->name)->toBe('Acme Danmark A/S')
        ->and($participant->capabilitySet)->toBeNull()
        ->and($participant->mode)->toBe(Mode::Live)
        ->and($participant->scheme)->toBe(IdentifierScheme::GLN)
        ->and($participant->identifier)->toBe('5790000435944')
        ->and($participant->fullIdentifier)->toBe('0088:5790000435944')
        ->and($participant->channels)->toBe([Channel::NemHandel]);
});
