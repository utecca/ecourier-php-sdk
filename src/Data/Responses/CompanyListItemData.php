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
            id: self::required($data, 'id'),
            name: self::required($data, 'name'),
            country: self::required($data, 'country'),
            companyNo: self::required($data, 'company_no'),
            webUrl: self::required($data, 'web_url'),
        );
    }
}
