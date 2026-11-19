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
            message: self::required($data, 'message'),
            code: self::optional($data, 'code'),
            location: self::optional($data, 'location'),
        );
    }
}
