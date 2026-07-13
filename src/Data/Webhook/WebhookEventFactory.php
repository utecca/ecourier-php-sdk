<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

use Ecourier\Enums\WebhookEventType;
use InvalidArgumentException;

final class WebhookEventFactory
{
    /** @var array<string, class-string<WebhookEvent>> */
    private const EVENT_MAP = [
        WebhookEventType::DocumentSendCreated->value => DocumentWebhook::class,
        WebhookEventType::DocumentSendDelivered->value => DocumentWebhook::class,
        WebhookEventType::DocumentSendFailed->value => DocumentWebhook::class,
        WebhookEventType::DocumentReceiveCreated->value => DocumentWebhook::class,
        WebhookEventType::DocumentReceiveReady->value => DocumentWebhook::class,
        WebhookEventType::DocumentReceiveDelivered->value => DocumentWebhook::class,
    ];

    public static function fromRequestBody(string $body): WebhookEvent
    {
        return self::fromArray(json_decode($body, true, flags: JSON_THROW_ON_ERROR));
    }

    public static function fromArray(array $data): WebhookEvent
    {
        $class = self::EVENT_MAP[$data['event'] ?? null] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException('Unknown webhook event.');
        }

        return $class::fromArray($data);
    }
}
