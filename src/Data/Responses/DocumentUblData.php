<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

readonly class DocumentUblData
{
    public function __construct(
        public ?string $id,
        public ?string $uuid,
        public ?string $profileId,
        public ?string $customizationId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            uuid: $data['uuid'] ?? null,
            profileId: $data['profile_id'] ?? null,
            customizationId: $data['customization_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'profile_id' => $this->profileId,
            'customization_id' => $this->customizationId,
        ];
    }
}
