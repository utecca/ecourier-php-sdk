<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Webhook;

use DateTimeImmutable;
use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Mode;
use Ecourier\Enums\WebhookEventType;

abstract readonly class WebhookEvent
{
    use ParsesResponseData;

    public function __construct(
        public string $eventId,
        public WebhookEventType $event,
        public DateTimeImmutable $occurredAt,
        public int $version,
        public string $teamId,
        public Mode $mode,
        public ?string $companyId,
    ) {}

    abstract public static function fromArray(array $data): static;

    public static function fromRequestBody(string $body): static
    {
        return static::fromArray(WebhookEventFactory::decode($body));
    }
}
