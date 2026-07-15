<?php

declare(strict_types=1);

use Ecourier\Data\Webhook\DocumentWebhook;
use Ecourier\Data\Webhook\WebhookEventFactory;
use Ecourier\Enums\Channel;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\Mode;
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
    expect($webhook->document->receiver->identifier)->toBe('87654321');
    expect($webhook->document->ubl->id)->toBe('INV-2024-001');
});

it('serializes document webhook data with wire keys', function () {
    $result = DocumentWebhook::fromRequestBody(webhookBody())->toArray();

    expect($result['event_id'])->toBe('evt_01hxyz');
    expect($result['payload']['document']['dashboard_url'])->toBe('https://app.ecourier.test/documents/doc_01xyz');
    expect($result['payload']['document']['latest_e2e_message_uuid'])->toBe('msg_01abc');
    expect($result['payload']['document']['sender']['scheme'])->toBe('DK:CVR');
    expect($result['payload']['document']['receiver']['identifier'])->toBe('87654321');
    expect($result['payload']['document']['ubl']['profile_id'])->toBe('urn:www.nesubl.eu:profiles:profile5:ver2.0');
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
    expect($webhook->toArray()['payload']['document']['transmitted_at'])->toBeNull();
});

it('throws on invalid webhook JSON', function () {
    WebhookEventFactory::fromRequestBody('{');
})->throws(JsonException::class);

it('throws on unknown webhook events', function () {
    WebhookEventFactory::fromRequestBody(webhookBody('Document.Unknown'));
})->throws(InvalidArgumentException::class);
