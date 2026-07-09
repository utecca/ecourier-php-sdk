<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data\Webhook;

class UblData
{
    public function __construct(
        public readonly string $id,
        public readonly string $issueDate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            issueDate: $data['issue_date'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'issue_date' => $this->issueDate,
        ];
    }
}
