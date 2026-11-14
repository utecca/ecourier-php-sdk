<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\DocumentType;

readonly class DocumentListItemData
{
    public function __construct(
        public string $id,
        public DocumentStatus $status,
        public Channel $channel,
        public Direction $direction,
        public DocumentType $type,
        public DateTimeImmutable $createdAt,
        public ?DocumentCompanyData $company,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: DocumentStatus::from($data['status']),
            channel: Channel::from($data['channel']),
            direction: Direction::from($data['direction']),
            type: DocumentType::from($data['type']),
            createdAt: new DateTimeImmutable($data['created_at']),
            company: isset($data['company']) ? DocumentCompanyData::fromArray($data['company']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'channel' => $this->channel->value,
            'direction' => $this->direction->value,
            'type' => $this->type->value,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:sP'),
            'company' => $this->company?->toArray(),
        ];
    }
}
