<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

use DateTimeImmutable;
use Ecourier\Enums\Mode;
use Ecourier\Enums\WebhookEventType;

final class DocumentWebhook extends WebhookEvent
{
    public function __construct(
        string $eventId,
        WebhookEventType $event,
        DateTimeImmutable $occurredAt,
        int $version,
        string $teamId,
        Mode $mode,
        string $companyId,
        public readonly DocumentData $document,
    ) {
        parent::__construct($eventId, $event, $occurredAt, $version, $teamId, $mode, $companyId);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            eventId: $data['event_id'],
            event: WebhookEventType::from($data['event']),
            occurredAt: new DateTimeImmutable($data['occurred_at']),
            version: (int) $data['version'],
            teamId: $data['team_id'],
            mode: Mode::from($data['mode']),
            companyId: $data['company_id'],
            document: DocumentData::fromArray($data['payload']['document']),
        );
    }

    protected function payloadToArray(): array
    {
        return [
            'document' => $this->document->toArray(),
        ];
    }
}
