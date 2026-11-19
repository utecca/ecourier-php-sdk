<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Webhook;

use DateTimeImmutable;
use Ecourier\Data\Responses\DocumentData;
use Ecourier\Enums\Mode;
use Ecourier\Enums\WebhookEventType;

final readonly class DocumentWebhook extends WebhookEvent
{
    public function __construct(
        string $eventId,
        WebhookEventType $event,
        DateTimeImmutable $occurredAt,
        int $version,
        string $teamId,
        Mode $mode,
        ?string $companyId,
        public DocumentData $document,
    ) {
        parent::__construct($eventId, $event, $occurredAt, $version, $teamId, $mode, $companyId);
    }

    public static function fromArray(array $data): static
    {
        return new self(
            eventId: self::required($data, 'event_id'),
            event: WebhookEventType::from(self::required($data, 'event')),
            occurredAt: self::date($data, 'occurred_at'),
            version: (int) self::required($data, 'version'),
            teamId: self::required($data, 'team_id'),
            mode: self::requiredEnum($data, 'mode', Mode::class),
            companyId: self::optional($data, 'company_id'),
            document: DocumentData::fromArray(self::required(self::required($data, 'payload'), 'document')),
        );
    }
}
