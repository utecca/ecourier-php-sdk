<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

use DateTimeImmutable;
use Ecourier\Sdk\Enums\IdentifierScheme;

class ParticipantData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?IdentifierScheme $scheme = null,
        public readonly ?string $endpoint = null,
        public readonly ?string $country = null,
        public readonly ?array $documentTypes = null,
        public readonly ?DateTimeImmutable $createdAt = null,
        public readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            scheme: isset($data['scheme']) ? IdentifierScheme::tryFrom($data['scheme']) : null,
            endpoint: $data['endpoint'] ?? null,
            country: $data['country'] ?? null,
            documentTypes: $data['document_types'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'scheme' => $this->scheme?->value,
            'endpoint' => $this->endpoint,
            'country' => $this->country,
            'document_types' => $this->documentTypes,
            'created_at' => $this->createdAt?->format('Y-m-d\TH:i:s\Z'),
            'updated_at' => $this->updatedAt?->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
