<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Mode;

final readonly class ParticipantLookupData
{
    use ParsesResponseData;

    public function __construct(
        public Channel $channel,
        public Mode $mode,
        public string $entityName,
        public ?string $country,
        public string $registrationDate,
        public string $orgNo,
        public string $registryUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            channel: self::requiredEnum($data, 'channel', Channel::class),
            mode: self::requiredEnum($data, 'mode', Mode::class),
            entityName: self::required($data, 'entityName'),
            country: self::optional($data, 'country'),
            registrationDate: self::required($data, 'registrationDate'),
            orgNo: self::required($data, 'orgNo'),
            registryUrl: self::required($data, 'registryUrl'),
        );
    }
}
