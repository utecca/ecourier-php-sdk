<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

use DateTimeImmutable;

class DocumentData
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $direction,
        public readonly ?string $type = null,
        public readonly ?string $channel = null,
        public readonly ?string $reference = null,
        public readonly ?DateTimeImmutable $issueDate = null,
        public readonly ?float $totalAmount = null,
        public readonly ?string $currency = null,
        public readonly ?PartyData $sender = null,
        public readonly ?PartyData $receiver = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
        public readonly ?DateTimeImmutable $deliveredAt = null,
        public readonly ?array $errors = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status'],
            direction: $data['direction'],
            type: $data['type'] ?? null,
            channel: $data['channel'] ?? null,
            reference: $data['reference'] ?? null,
            issueDate: isset($data['issue_date']) ? new DateTimeImmutable($data['issue_date']) : null,
            totalAmount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            currency: $data['currency'] ?? null,
            sender: isset($data['sender']) ? PartyData::fromArray($data['sender']) : null,
            receiver: isset($data['receiver']) ? PartyData::fromArray($data['receiver']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
            deliveredAt: isset($data['delivered_at']) ? new DateTimeImmutable($data['delivered_at']) : null,
            errors: $data['errors'] ?? null,
        );
    }
}
