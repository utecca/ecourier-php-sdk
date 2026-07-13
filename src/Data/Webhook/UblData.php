<?php

declare(strict_types=1);

namespace Ecourier\Data\Webhook;

class UblData
{
    public function __construct(
        public readonly string $id,
        public readonly string $profileId,
        public readonly string $customizationId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            profileId: $data['profile_id'],
            customizationId: $data['customization_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'profile_id' => $this->profileId,
            'customization_id' => $this->customizationId,
        ];
    }
}
