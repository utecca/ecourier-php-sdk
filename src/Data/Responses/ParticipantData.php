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
            id: self::string($data, 'id'),
            company: ParticipantCompanyData::fromArray(self::object($data, 'company')),
            capabilitySet: isset($data['capability_set']) ? ParticipantCapabilitySetData::fromArray(self::object($data, 'capability_set')) : null,
            mode: self::requiredEnum($data, 'mode', Mode::class),
            scheme: self::enum(IdentifierScheme::class, self::optional($data, 'scheme')),
            identifier: self::string($data, 'identifier'),
            schemeIcd: self::string($data, 'scheme_icd'),
            fullIdentifier: self::string($data, 'full_identifier'),
            channels: self::enumList($data, 'channels', Channel::class),
            createdAt: self::date($data, 'created_at'),
            updatedAt: self::date($data, 'updated_at'),
            webUrl: self::string($data, 'web_url'),
        );
    }
}
