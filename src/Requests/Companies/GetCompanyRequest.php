<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Data\Responses\CompanyData;
use Ecourier\Requests\Concerns\EncodesPathParameters;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetCompanyRequest extends Request
{
    use EncodesPathParameters;

    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $company,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/companies/' . self::pathParameter($this->company, 'company ID');
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
