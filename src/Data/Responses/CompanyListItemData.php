<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses;

use Ecourier\Data\Responses\Concerns\ParsesResponseData;

final readonly class CompanyListItemData
{
    use ParsesResponseData;

    /**
     * @param string $webUrl Link to the company in the eCourier portal.
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $country,
        public string $companyNo,
        public string $webUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: self::string($data, 'id'),
            name: self::string($data, 'name'),
            country: self::string($data, 'country'),
            companyNo: self::string($data, 'company_no'),
            webUrl: self::string($data, 'web_url'),
        );
    }
}
