<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Channel;
use Ecourier\Enums\IdentifierScheme;
use Ecourier\Enums\Mode;

final readonly class ParticipantData
{
    use ParsesResponseData;

    /**
     * @param Channel[] $channels
     * @param string $webUrl Link to manage the participant in the eCourier portal. Participants have no page of their own, so it opens their company's participants tab.
     */
    public function __construct(
        public string $id,
        public ParticipantCompanyData $company,
        public ?ParticipantCapabilitySetData $capabilitySet,
        public Mode $mode,
        public ?IdentifierScheme $scheme,
        public string $identifier,
        public string $schemeIcd,
        public string $fullIdentifier,
        public array $channels,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public string $webUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::required($data, 'id'),
            company: ParticipantCompanyData::fromArray(self::required($data, 'company')),
            capabilitySet: isset($data['capability_set']) ? ParticipantCapabilitySetData::fromArray($data['capability_set']) : null,
            mode: self::requiredEnum($data, 'mode', Mode::class),
            scheme: self::enum(IdentifierScheme::class, self::optional($data, 'scheme')),
            identifier: self::required($data, 'identifier'),
            schemeIcd: self::required($data, 'scheme_icd'),
            fullIdentifier: self::required($data, 'full_identifier'),
            channels: self::enumList($data, 'channels', Channel::class),
            createdAt: self::date($data, 'created_at'),
            updatedAt: self::date($data, 'updated_at'),
            webUrl: self::required($data, 'web_url'),
        );
    }
}
