<?php

declare(strict_types=1);

namespace Ecourier\Data\Requests;

use Ecourier\Data\Responses\CompanyAuthorisationSignerData;

final readonly class CreateCompanyData
{
    /**
     * The signer may only be omitted by teams that submit their own signed authorisation PDF.
     */
    public function __construct(
        public string $name,
        public string $country,
        public string $companyNo,
        public ?CompanyAuthorisationSignerData $signer = null,
        public ?string $redirectUrl = null,
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
