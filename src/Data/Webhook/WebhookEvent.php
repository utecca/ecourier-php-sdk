<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

use InvalidArgumentException;

class WebhookEvent
{
    private const DOCUMENT_EVENTS = [
        'Document.Send.Created',
        'Document.Send.Delivered',
        'Document.Send.Failed',
        'Document.Receive.Created',
        'Document.Receive.Ready',
        'Document.Receive.Delivered',
    ];

    public static function fromRequestBody(string $body): DocumentWebhook
    {
        return self::fromArray(json_decode($body, true, flags: JSON_THROW_ON_ERROR));
    }

    public static function fromArray(array $data): DocumentWebhook
    {
        if (! in_array($data['event'] ?? null, self::DOCUMENT_EVENTS, true)) {
            throw new InvalidArgumentException('Unknown webhook event.');
        }

        return DocumentWebhook::fromArray($data);
    }
}
