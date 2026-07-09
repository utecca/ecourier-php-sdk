<?php

declare(strict_types=1);

namespace Ecourier\Data;

use DateTimeImmutable;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Currency;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;

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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'direction' => $this->direction->value,
            'type' => $this->type?->value,
            'channel' => $this->channel?->value,
            'reference' => $this->reference,
            'issue_date' => $this->issueDate?->format('Y-m-d'),
            'total_amount' => $this->totalAmount,
            'currency' => $this->currency?->value,
            'sender' => $this->sender?->toArray(),
            'receiver' => $this->receiver?->toArray(),
            'created_at' => $this->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $this->updatedAt?->format('Y-m-d\TH:i:s\Z'),
            'delivered_at' => $this->deliveredAt?->format('Y-m-d\TH:i:s\Z'),
            'errors' => $this->errors,
        ];
    }
}
