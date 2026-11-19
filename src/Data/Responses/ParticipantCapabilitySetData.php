<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class ParticipantCapabilitySetData
{
    use ParsesResponseData;

    public function __construct(
        public string $id,
        public string $name,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::required($data, 'id'),
            name: self::required($data, 'name'),
        );
    }
}
