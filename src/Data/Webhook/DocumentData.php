<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

use DateTimeImmutable;
use Ecourier\Enums\Channel;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;

class DocumentData
{
    public function __construct(
        public readonly string $id,
        public readonly string $dashboardUrl,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $transmittedAt,
        public readonly Channel $channel,
        public readonly DocumentStatus $status,
        public readonly DocumentType $type,
        public readonly string $latestE2eMessageUuid,
        public readonly string $latestE2eTransmissionId,
        public readonly ParticipantData $sender,
        public readonly ParticipantData $receiver,
        public readonly UblData $ubl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            dashboardUrl: $data['dashboard_url'],
            createdAt: new DateTimeImmutable($data['created_at']),
            transmittedAt: new DateTimeImmutable($data['transmitted_at']),
            channel: Channel::from($data['channel']),
            status: DocumentStatus::from($data['status']),
            type: DocumentType::from($data['type']),
            latestE2eMessageUuid: $data['latest_e2e_message_uuid'],
            latestE2eTransmissionId: $data['latest_e2e_transmission_id'],
            sender: ParticipantData::fromArray($data['sender']),
            receiver: ParticipantData::fromArray($data['receiver']),
            ubl: UblData::fromArray($data['ubl']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'dashboard_url' => $this->dashboardUrl,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
            'transmitted_at' => $this->transmittedAt->format('Y-m-d\TH:i:s\Z'),
            'channel' => $this->channel->value,
            'status' => $this->status->value,
            'type' => $this->type->value,
            'latest_e2e_message_uuid' => $this->latestE2eMessageUuid,
            'latest_e2e_transmission_id' => $this->latestE2eTransmissionId,
            'sender' => $this->sender->toArray(),
            'receiver' => $this->receiver->toArray(),
            'ubl' => $this->ubl->toArray(),
        ];
    }
}
