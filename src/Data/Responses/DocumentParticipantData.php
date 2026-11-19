<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\IdentifierScheme;

final readonly class DocumentParticipantData
{
    use ParsesResponseData;

    public function __construct(
        public ?IdentifierScheme $scheme,
        public string $identifier,
        public string $schemeIcd,
        public string $fullIdentifier,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            scheme: self::enum(IdentifierScheme::class, self::optional($data, 'scheme')),
            identifier: self::required($data, 'identifier'),
            schemeIcd: self::required($data, 'scheme_icd'),
            fullIdentifier: self::required($data, 'full_identifier'),
        );
    }
}
