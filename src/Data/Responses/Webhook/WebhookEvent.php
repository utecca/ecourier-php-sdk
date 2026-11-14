<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Webhook;

use DateTimeImmutable;
use Ecourier\Enums\Mode;
use Ecourier\Enums\WebhookEventType;

abstract readonly class WebhookEvent
{
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
