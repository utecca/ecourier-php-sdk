<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

use DateTimeImmutable;
use Ecourier\Enums\Mode;
use Ecourier\Enums\WebhookEventType;

abstract class WebhookEvent
{
    public function __construct(
        public readonly string $eventId,
        public readonly WebhookEventType $event,
        public readonly DateTimeImmutable $occurredAt,
        public readonly int $version,
        public readonly string $teamId,
        public readonly Mode $mode,
        public readonly string $companyId,
    ) {}

    abstract public static function fromArray(array $data): static;

    abstract protected function payloadToArray(): array;

    public static function fromRequestBody(string $body): static
    {
        return static::fromArray(json_decode($body, true, flags: JSON_THROW_ON_ERROR));
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event' => $this->event->value,
            'occurred_at' => $this->occurredAt->format('Y-m-d\TH:i:s\Z'),
            'version' => $this->version,
            'team_id' => $this->teamId,
            'mode' => $this->mode->value,
            'company_id' => $this->companyId,
            'payload' => $this->payloadToArray(),
        ];
    }
}
