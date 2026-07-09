<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Data\CompanyData;
use Ecourier\Data\CreateCompanyData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class CreateCompanyRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly CreateCompanyData $company,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/companies';
    }

    protected function defaultBody(): array
    {
        return $this->company->toArray();
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
