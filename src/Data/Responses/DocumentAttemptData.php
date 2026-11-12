<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Enums\DocumentAttemptStatus;

readonly class DocumentAttemptData
{
    public function __construct(
        public DocumentAttemptStatus $status,
        public ?string $errorMessage,
        public string $e2eMessageUuid,
        public ?string $e2eTransmissionId,
        public DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: DocumentAttemptStatus::from($data['status']),
            errorMessage: $data['error_message'],
            e2eMessageUuid: $data['e2e_message_uuid'],
            e2eTransmissionId: $data['e2e_transmission_id'],
            createdAt: new DateTimeImmutable($data['created_at']),
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'error_message' => $this->errorMessage,
            'e2e_message_uuid' => $this->e2eMessageUuid,
            'e2e_transmission_id' => $this->e2eTransmissionId,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
