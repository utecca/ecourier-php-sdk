<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class DocumentUblData
{
    use ParsesResponseData;

    public function __construct(
        public ?string $id,
        public ?string $uuid,
        public ?string $profileId,
        public ?string $customizationId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::optional($data, 'id'),
            uuid: self::optional($data, 'uuid'),
            profileId: self::optional($data, 'profile_id'),
            customizationId: self::optional($data, 'customization_id'),
        );
    }
}
