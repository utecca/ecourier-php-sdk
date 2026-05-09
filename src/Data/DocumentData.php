<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

use DateTimeImmutable;
use Ecourier\Sdk\Enums\Channel;
use Ecourier\Sdk\Enums\Currency;
use Ecourier\Sdk\Enums\Direction;
use Ecourier\Sdk\Enums\DocumentStatus;
use Ecourier\Sdk\Enums\DocumentType;

class DocumentData
{
    public function __construct(
        public readonly string $id,
        public readonly DocumentStatus $status,
        public readonly Direction $direction,
        public readonly ?DocumentType $type = null,
        public readonly ?Channel $channel = null,
        public readonly ?string $reference = null,
        public readonly ?DateTimeImmutable $issueDate = null,
        public readonly ?float $totalAmount = null,
        public readonly ?Currency $currency = null,
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
            status: DocumentStatus::from($data['status']),
            direction: Direction::from($data['direction']),
            type: isset($data['type']) ? DocumentType::tryFrom($data['type']) : null,
            channel: isset($data['channel']) ? Channel::tryFrom($data['channel']) : null,
            reference: $data['reference'] ?? null,
            issueDate: isset($data['issue_date']) ? new DateTimeImmutable($data['issue_date']) : null,
            totalAmount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            currency: isset($data['currency']) ? Currency::tryFrom($data['currency']) : null,
            sender: isset($data['sender']) ? PartyData::fromArray($data['sender']) : null,
            receiver: isset($data['receiver']) ? PartyData::fromArray($data['receiver']) : null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
            deliveredAt: isset($data['delivered_at']) ? new DateTimeImmutable($data['delivered_at']) : null,
            errors: $data['errors'] ?? null,
        );
    }
}
