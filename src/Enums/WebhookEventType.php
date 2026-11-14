<?php

declare(strict_types=1);

namespace Ecourier\Enums;

use Ecourier\Data\Responses\Webhook\DocumentWebhook;
use Ecourier\Data\Responses\Webhook\WebhookEvent;

enum WebhookEventType: string
{
    case DocumentSendCreated = 'Document.Send.Created';
    case DocumentSendDelivered = 'Document.Send.Delivered';
    case DocumentSendFailed = 'Document.Send.Failed';
    case DocumentReceiveCreated = 'Document.Receive.Created';
    case DocumentReceiveReady = 'Document.Receive.Ready';
    case DocumentReceiveDelivered = 'Document.Receive.Delivered';

    /** @return class-string<WebhookEvent> */
    public function dtoClass(): string
    {
        return match ($this) {
            self::DocumentSendCreated,
            self::DocumentSendDelivered,
            self::DocumentSendFailed,
            self::DocumentReceiveCreated,
            self::DocumentReceiveReady,
            self::DocumentReceiveDelivered => DocumentWebhook::class,
        };
    }
}
