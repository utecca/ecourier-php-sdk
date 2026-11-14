<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use DateTimeImmutable;
use Ecourier\Enums\Mode;

readonly class CompanyData
{
    /** @param CompanyParticipantData[] $participants */
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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            mode: Mode::from($data['mode']),
            companyNo: $data['company_no'],
            createdAt: new DateTimeImmutable($data['created_at']),
            updatedAt: new DateTimeImmutable($data['updated_at']),
            country: $data['country'],
            authorisation: $data['authorisation'] !== null ? CompanyAuthorisationData::fromArray($data['authorisation']) : null,
            participants: array_map(fn(array $participant) => CompanyParticipantData::fromArray($participant), $data['participants']),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mode' => $this->mode->value,
            'company_no' => $this->companyNo,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:sP'),
            'updated_at' => $this->updatedAt->format('Y-m-d\TH:i:sP'),
            'country' => $this->country,
            'authorisation' => $this->authorisation?->toArray(),
            'participants' => array_map(fn(CompanyParticipantData $participant) => $participant->toArray(), $this->participants),
        ];
    }
}
