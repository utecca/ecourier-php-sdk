<?php

declare(strict_types=1);

namespace Ecourier\Enums;

enum WebhookEventType: string
{
    case DocumentSendCreated = 'Document.Send.Created';
    case DocumentSendDelivered = 'Document.Send.Delivered';
    case DocumentSendFailed = 'Document.Send.Failed';
    case DocumentReceiveCreated = 'Document.Receive.Created';
    case DocumentReceiveReady = 'Document.Receive.Ready';
    case DocumentReceiveDelivered = 'Document.Receive.Delivered';
}
