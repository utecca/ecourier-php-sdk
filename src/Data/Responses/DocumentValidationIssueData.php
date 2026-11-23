<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class DocumentValidationIssueData
{
    use ParsesResponseData;

    public function __construct(
        public string $message,
        public ?string $code,
        public ?string $location,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            message: self::string($data, 'message'),
            code: self::optionalString($data, 'code'),
            location: self::optionalString($data, 'location'),
        );
    }
}
