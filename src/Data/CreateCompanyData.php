<?php

declare(strict_types=1);

namespace Ecourier\Data;

class CreateCompanyData
{
    /**
     * The signer may only be omitted by teams that submit their own signed authorisation PDF.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $country,
        public readonly string $companyNo,
        public readonly ?CompanyAuthorisationSignerData $signer = null,
        public readonly ?string $redirectUrl = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'country' => $this->country,
            'company_no' => $this->companyNo,
        ];

        if ($this->signer !== null) {
            $data['signer'] = $this->signer->toArray();
        }

        if ($this->redirectUrl !== null) {
            $data['redirect_url'] = $this->redirectUrl;
        }

        return $data;
    }
}
