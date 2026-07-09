<?php

declare(strict_types=1);

namespace Ecourier\Data;

class CreateCompanyData
{
    public function __construct(
        public readonly string $name,
        public readonly string $country,
        public readonly string $companyNo,
        public readonly CompanyAuthorisationSignerData $signer,
        public readonly ?string $parentId = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'country' => $this->country,
            'company_no' => $this->companyNo,
            'signer' => $this->signer->toArray(),
        ];

        if ($this->parentId !== null) {
            $data['parent_id'] = $this->parentId;
        }

        return $data;
    }
}
