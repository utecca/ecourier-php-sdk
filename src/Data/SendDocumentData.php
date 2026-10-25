<?php

declare(strict_types=1);

namespace Ecourier\Data;

class SendDocumentData
{
    public function __construct(
        public readonly string $id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
