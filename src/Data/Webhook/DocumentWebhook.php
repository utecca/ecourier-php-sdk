<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Webhook;

use DateTimeImmutable;

class DocumentWebhook
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $event,
        public readonly DateTimeImmutable $occurredAt,
        public readonly int $version,
        public readonly string $teamId,
        public readonly string $mode,
        public readonly string $companyId,
        public readonly DocumentData $document,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            eventId: $data['event_id'],
            event: $data['event'],
            occurredAt: new DateTimeImmutable($data['occurred_at']),
            version: (int) $data['version'],
            teamId: $data['team_id'],
            mode: $data['mode'],
            companyId: $data['company_id'],
            document: DocumentData::fromArray($data['payload']['document']),
        );
    }

    public static function fromRequestBody(string $body): self
    {
        return self::fromArray(json_decode($body, true, flags: JSON_THROW_ON_ERROR));
    }

    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event' => $this->event,
            'occurred_at' => $this->occurredAt->format('Y-m-d\TH:i:s\Z'),
            'version' => $this->version,
            'team_id' => $this->teamId,
            'mode' => $this->mode,
            'company_id' => $this->companyId,
            'payload' => [
                'document' => $this->document->toArray(),
            ],
        ];
    }
}
