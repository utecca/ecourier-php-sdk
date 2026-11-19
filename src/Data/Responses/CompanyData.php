<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Data\Responses\Concerns\ParsesResponseData;
use Ecourier\Enums\Mode;

final readonly class CompanyData
{
    use ParsesResponseData;

    /**
     * @param CompanyParticipantData[] $participants
     * @param string $webUrl Link to the company in the eCourier portal.
     */
    public function __construct(
        public string $id,
        public string $name,
        public Mode $mode,
        public string $companyNo,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public string $country,
        public ?CompanyAuthorisationData $authorisation,
        public array $participants,
        public string $webUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::required($data, 'id'),
            name: self::required($data, 'name'),
            mode: self::requiredEnum($data, 'mode', Mode::class),
            companyNo: self::required($data, 'company_no'),
            createdAt: self::date($data, 'created_at'),
            updatedAt: self::date($data, 'updated_at'),
            country: self::required($data, 'country'),
            authorisation: isset($data['authorisation']) ? CompanyAuthorisationData::fromArray($data['authorisation']) : null,
            participants: array_map(fn(array $participant) => CompanyParticipantData::fromArray($participant), self::list($data, 'participants')),
            webUrl: self::required($data, 'web_url'),
        );
    }
}
