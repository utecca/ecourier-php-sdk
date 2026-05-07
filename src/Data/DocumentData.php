<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

class DocumentData
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $direction,
        public readonly ?string $type = null,
        public readonly ?string $channel = null,
        public readonly ?string $reference = null,
        public readonly ?string $issueDate = null,
        public readonly ?float $totalAmount = null,
        public readonly ?string $currency = null,
        public readonly ?PartyData $sender = null,
        public readonly ?PartyData $receiver = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly ?string $deliveredAt = null,
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
            issueDate: $data['issue_date'] ?? null,
            totalAmount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            currency: $data['currency'] ?? null,
            sender: isset($data['sender']) ? PartyData::fromArray($data['sender']) : null,
            receiver: isset($data['receiver']) ? PartyData::fromArray($data['receiver']) : null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
            deliveredAt: $data['delivered_at'] ?? null,
            errors: $data['errors'] ?? null,
        );
    }
}
