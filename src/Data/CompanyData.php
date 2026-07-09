<?php

declare(strict_types=1);

namespace Ecourier\Data;

use DateTimeImmutable;
use Ecourier\Enums\Mode;

class CompanyData
{
    /** @param string[] $children @param CompanyParticipantData[] $participants */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly Mode $mode,
        public readonly string $companyNo,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?string $parentId,
        public readonly array $children,
        public readonly string $country,
        public readonly ?CompanyAuthorisationData $authorisation,
        public readonly array $participants,
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
            parentId: $data['parent_id'],
            children: $data['children'],
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
            'parent_id' => $this->parentId,
            'children' => $this->children,
            'country' => $this->country,
            'authorisation' => $this->authorisation?->toArray(),
            'participants' => array_map(fn(CompanyParticipantData $participant) => $participant->toArray(), $this->participants),
        ];
    }
}
