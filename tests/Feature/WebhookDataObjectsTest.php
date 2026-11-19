<?php

declare(strict_types=1);

use Ecourier\Data\Responses\Webhook\DocumentWebhook;
use Ecourier\Data\Responses\Webhook\WebhookEventFactory;
use Ecourier\Data\Responses\Webhook\WebhookSignature;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;
use Ecourier\Enums\WebhookEventType;

function webhookBody(string $event = 'Document.Send.Created'): string
{
    $data = json_decode(file_get_contents(__DIR__ . '/../Fixtures/webhook-document.json'), true);
    $data['event'] = $event;

    return json_encode($data, JSON_THROW_ON_ERROR);
}

it('parses each document webhook event from a request body', function (string $event) {
    $webhook = DocumentWebhook::fromRequestBody(webhookBody($event));

    expect($webhook)->toBeInstanceOf(DocumentWebhook::class);
    expect($webhook->event)->toBe(WebhookEventType::from($event));
})->with([
    'Document.Send.Created',
    'Document.Send.Delivered',
    'Document.Send.Failed',
    'Document.Receive.Created',
    'Document.Receive.Ready',
    'Document.Receive.Delivered',
]);

it('maps known document webhook events to the document webhook DTO', function (string $event) {
    expect(WebhookEventFactory::fromRequestBody(webhookBody($event)))->toBeInstanceOf(DocumentWebhook::class);
})->with([
    'Document.Send.Created',
    'Document.Send.Delivered',
    'Document.Send.Failed',
    'Document.Receive.Created',
    'Document.Receive.Ready',
    'Document.Receive.Delivered',
]);

it('maps decoded webhook arrays to the document webhook DTO', function () {
    $data = json_decode(webhookBody(), true, flags: JSON_THROW_ON_ERROR);

    expect(WebhookEventFactory::fromArray($data))->toBeInstanceOf(DocumentWebhook::class);
});

it('maps nested document webhook data', function () {
    $webhook = DocumentWebhook::fromRequestBody(webhookBody());

    expect($webhook->eventId)->toBe('evt_01hxyz');
    expect($webhook->event)->toBe(WebhookEventType::DocumentSendCreated);
    expect($webhook->mode)->toBe(Mode::Test);
    expect($webhook->occurredAt->format('Y-m-d\TH:i:s\Z'))->toBe('2024-06-01T10:06:00Z');
    expect($webhook->document->id)->toBe('doc_01xyz');
    expect($webhook->document->channel)->toBe(Channel::NemHandel);
    expect($webhook->document->status)->toBe(DocumentStatus::Delivered);
    expect($webhook->document->type)->toBe(DocumentType::Invoice);
    expect($webhook->document->sender->identifier)->toBe('12345678');
    expect($webhook->document->recipient->identifier)->toBe('87654321');
    expect($webhook->document->ubl->id)->toBe('INV-2024-001');
    expect($webhook->document->validation?->errors)->toBe([]);
    expect($webhook->document->validation?->warnings)->toBe([]);
    expect($webhook->document->mode)->toBe(Mode::Test);
    expect($webhook->document->direction)->toBe(Direction::Send);
    expect($webhook->document->submissionFormat)->toBe(SubmissionFormat::XML);
    expect($webhook->document->company?->name)->toBe('Acme Danmark A/S');
});

it('parses a document webhook with a not-yet-transmitted document', function () {
    $data = json_decode(webhookBody(), true, flags: JSON_THROW_ON_ERROR);
    $data['payload']['document']['status'] = 'Pending';
    $data['payload']['document']['transmitted_at'] = null;
    $data['payload']['document']['latest_e2e_message_uuid'] = null;
    $data['payload']['document']['latest_e2e_transmission_id'] = null;

    $webhook = DocumentWebhook::fromArray($data);

    expect($webhook->document->transmittedAt)->toBeNull();
    expect($webhook->document->latestE2eMessageUuid)->toBeNull();
    expect($webhook->document->latestE2eTransmissionId)->toBeNull();
});

it('parses a document webhook with unextracted UBL data', function () {
    $data = json_decode(webhookBody(), true, flags: JSON_THROW_ON_ERROR);
    $data['payload']['document']['ubl'] = [
        'id' => null,
        'profile_id' => null,
        'customization_id' => null,
    ];

    $webhook = DocumentWebhook::fromArray($data);

    expect($webhook->document->ubl->id)->toBeNull();
    expect($webhook->document->ubl->uuid)->toBeNull();
    expect($webhook->document->ubl->profileId)->toBeNull();
    expect($webhook->document->ubl->customizationId)->toBeNull();
});

it('parses a document webhook with a UBL uuid', function () {
    $data = json_decode(webhookBody(), true, flags: JSON_THROW_ON_ERROR);
    $data['payload']['document']['ubl']['uuid'] = '660e8400-e29b-41d4-a716-446655440007';

    $webhook = DocumentWebhook::fromArray($data);

    expect($webhook->document->ubl->uuid)->toBe('660e8400-e29b-41d4-a716-446655440007');
});

it('throws on invalid webhook JSON', function () {
    WebhookEventFactory::fromRequestBody('{');
})->throws(JsonException::class);

it('throws on unknown webhook events', function () {
    WebhookEventFactory::fromRequestBody(webhookBody('Document.Unknown'));
})->throws(InvalidArgumentException::class);

it('accepts a null company id for deleted companies', function () {
    $data = json_decode(webhookBody(), true);
    $data['company_id'] = null;

    $webhook = DocumentWebhook::fromArray($data);

    expect($webhook->companyId)->toBeNull();
});

it('verifies a valid webhook signature', function () {
    $body = webhookBody();
    $signature = hash_hmac('sha256', $body, 'whsec_test');

    expect(WebhookSignature::verify($body, $signature, 'whsec_test'))->toBeTrue();
});

it('rejects an invalid webhook signature', function (?string $signature, string $secret) {
    expect(WebhookSignature::verify(webhookBody(), $signature, $secret))->toBeFalse();
})->with([
    'wrong secret' => [fn() => hash_hmac('sha256', webhookBody(), 'whsec_other'), 'whsec_test'],
    'tampered body' => [fn() => hash_hmac('sha256', webhookBody() . ' ', 'whsec_test'), 'whsec_test'],
    'missing header' => [null, 'whsec_test'],
    'empty header' => ['', 'whsec_test'],
    'empty secret' => [fn() => hash_hmac('sha256', webhookBody(), ''), ''],
]);

it('verifies the current request from an explicitly passed body and signature', function () {
    $body = webhookBody();
    $signature = hash_hmac('sha256', $body, 'whsec_test');

    expect(WebhookSignature::verifyCurrentRequest('whsec_test', $body, $signature))->toBeTrue();
    expect(WebhookSignature::verifyCurrentRequest('whsec_other', $body, $signature))->toBeFalse();
});

it('falls back to the Signature header in $_SERVER when none is passed', function () {
    $body = webhookBody();
    $_SERVER['HTTP_SIGNATURE'] = hash_hmac('sha256', $body, 'whsec_test');

    try {
        expect(WebhookSignature::verifyCurrentRequest('whsec_test', $body))->toBeTrue();
    } finally {
        unset($_SERVER['HTTP_SIGNATURE']);
    }
});
