<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Webhook;

use Ecourier\Enums\WebhookEventType;
use InvalidArgumentException;

final class WebhookEventFactory
{
    public static function fromRequestBody(string $body): WebhookEvent
    {
        return self::fromArray(self::decode($body));
    }

    /**
     * @internal
     *
     * @return array<array-key, mixed>
     */
    public static function decode(string $body): array
    {
        $data = json_decode($body, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new InvalidArgumentException('The webhook body is not a JSON object.');
        }

        return $data;
    }

    public static function fromArray(array $data): WebhookEvent
    {
        $event = $data['event'] ?? null;
        $type = is_string($event) ? WebhookEventType::tryFrom($event) : null;

        if ($type === null) {
            throw new InvalidArgumentException('Unknown webhook event.');
        }

        return $type->dtoClass()::fromArray($data);
    }
}
