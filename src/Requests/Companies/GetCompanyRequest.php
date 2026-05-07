<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Companies;

use Ecourier\Sdk\Data\CompanyData;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetCompanyRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $company,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/companies/{$this->company}";
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
