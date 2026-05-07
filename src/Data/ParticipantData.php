<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

class ParticipantData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $scheme = null,
        public readonly ?string $endpoint = null,
        public readonly ?string $country = null,
        public readonly ?array $documentTypes = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            scheme: $data['scheme'] ?? null,
            endpoint: $data['endpoint'] ?? null,
            country: $data['country'] ?? null,
            documentTypes: $data['document_types'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }
}
