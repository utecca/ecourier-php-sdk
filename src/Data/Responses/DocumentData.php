<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;
use Ecourier\Enums\Mode;
use Ecourier\Enums\SubmissionFormat;

readonly class DocumentData
{
    /** @param DocumentAttemptData[] $attempts */
    public function __construct(
        public string $id,
        public DocumentStatus $status,
        public Channel $channel,
        public Mode $mode,
        public Direction $direction,
        public DocumentType $type,
        public SubmissionFormat $submissionFormat,
        public DocumentParticipantData $sender,
        public DocumentParticipantData $recipient,
        public string $webUrl,
        public DateTimeImmutable $createdAt,
        public DocumentUblData $ubl,
        public DocumentValidationData $validation,
        public array $attempts,
        public ?string $latestE2eMessageUuid,
        public ?string $latestE2eTransmissionId,
        public ?DocumentCompanyData $company,
        public ?DateTimeImmutable $transmittedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: DocumentStatus::from($data['status']),
            channel: Channel::from($data['channel']),
            mode: Mode::from($data['mode']),
            direction: Direction::from($data['direction']),
            type: DocumentType::from($data['type']),
            submissionFormat: SubmissionFormat::from($data['submission_format']),
            sender: DocumentParticipantData::fromArray($data['sender']),
            recipient: DocumentParticipantData::fromArray($data['recipient']),
            webUrl: $data['dashboard_url'],
            createdAt: new DateTimeImmutable($data['created_at']),
            ubl: DocumentUblData::fromArray($data['ubl']),
            validation: DocumentValidationData::fromArray($data['validation']),
            attempts: array_map(fn(array $attempt) => DocumentAttemptData::fromArray($attempt), $data['attempts'] ?? []),
            latestE2eMessageUuid: $data['latest_e2e_message_uuid'] ?? null,
            latestE2eTransmissionId: $data['latest_e2e_transmission_id'] ?? null,
            company: isset($data['company']) ? DocumentCompanyData::fromArray($data['company']) : null,
            transmittedAt: isset($data['transmitted_at']) ? new DateTimeImmutable($data['transmitted_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'channel' => $this->channel->value,
            'mode' => $this->mode->value,
            'direction' => $this->direction->value,
            'type' => $this->type->value,
            'submission_format' => $this->submissionFormat->value,
            'sender' => $this->sender->toArray(),
            'recipient' => $this->recipient->toArray(),
            'latest_e2e_message_uuid' => $this->latestE2eMessageUuid,
            'latest_e2e_transmission_id' => $this->latestE2eTransmissionId,
            'company' => $this->company?->toArray(),
            'dashboard_url' => $this->webUrl,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
            'transmitted_at' => $this->transmittedAt?->format('Y-m-d\TH:i:s\Z'),
            'ubl' => $this->ubl->toArray(),
            'validation' => $this->validation->toArray(),
            'attempts' => array_map(fn(DocumentAttemptData $attempt) => $attempt->toArray(), $this->attempts),
        ];
    }
}
