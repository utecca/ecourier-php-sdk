<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;

final readonly class CompanyParticipantData
{
    use ParsesResponseData;

    /** @param Channel[] $channels */
    public function __construct(
        public string $id,
        public ?IdentifierScheme $scheme,
        public string $identifier,
        public string $schemeIcd,
        public string $fullIdentifier,
        public array $channels,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::required($data, 'id'),
            scheme: self::enum(IdentifierScheme::class, self::optional($data, 'scheme')),
            identifier: self::required($data, 'identifier'),
            schemeIcd: self::required($data, 'scheme_icd'),
            fullIdentifier: self::required($data, 'full_identifier'),
            channels: self::enumList($data, 'channels', Channel::class),
        );
    }
}
