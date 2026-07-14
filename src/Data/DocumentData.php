<?php

declare(strict_types=1);

namespace Ecourier\Data;

use DateTimeImmutable;
use Ecourier\Data\Invoice\ParticipantIdentifier;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

class DocumentData
{
    public function __construct(
        public readonly string $id,
        public readonly DocumentStatus $status,
        public readonly Channel $channel,
        public readonly ?Mode $mode,
        public readonly Direction $direction,
        public readonly DocumentType $type,
        public readonly ?SubmissionFormat $submissionFormat = null,
        public readonly ?ParticipantIdentifier $sender = null,
        public readonly ?ParticipantIdentifier $recipient = null,
        public readonly ?string $latestE2eMessageUuid = null,
        public readonly ?string $latestE2eTransmissionId = null,
        public readonly ?DocumentCompanyData $company = null,
        public readonly ?DateTimeImmutable $createdAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: DocumentStatus::from($data['status']),
            channel: Channel::from($data['channel']),
            mode: isset($data['mode']) ? Mode::from($data['mode']) : null,
            direction: Direction::from($data['direction']),
            type: DocumentType::from($data['type']),
            submissionFormat: isset($data['submission_format']) ? SubmissionFormat::from($data['submission_format']) : null,
            sender: isset($data['sender']) ? ParticipantIdentifier::fromArray($data['sender']) : null,
            recipient: isset($data['recipient']) ? ParticipantIdentifier::fromArray($data['recipient']) : null,
            latestE2eMessageUuid: $data['latest_e2e_message_uuid'] ?? null,
            latestE2eTransmissionId: $data['latest_e2e_transmission_id'] ?? null,
            company: isset($data['company']) && $data['company'] !== null ? DocumentCompanyData::fromArray($data['company']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'channel' => $this->channel->value,
            'mode' => $this->mode?->value,
            'direction' => $this->direction->value,
            'type' => $this->type->value,
            'submission_format' => $this->submissionFormat?->value,
            'sender' => $this->sender?->toArray(),
            'recipient' => $this->recipient?->toArray(),
            'latest_e2e_message_uuid' => $this->latestE2eMessageUuid,
            'latest_e2e_transmission_id' => $this->latestE2eTransmissionId,
            'company' => $this->company?->toArray(),
            'created_at' => $this->createdAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
