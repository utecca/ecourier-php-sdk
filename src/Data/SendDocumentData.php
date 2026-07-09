<?php

declare(strict_types=1);

namespace Ecourier\Data;

class SendDocumentData
{
    public function __construct(
        public readonly string $id,
        public readonly string $e2eMessageUuid,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            e2eMessageUuid: $data['e2e_message_uuid'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'e2e_message_uuid' => $this->e2eMessageUuid,
        ];
    }
}
