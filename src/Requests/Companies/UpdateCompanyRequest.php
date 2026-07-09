<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Data\CompanyData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateCompanyRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(
        private readonly string $company,
        private readonly string $name,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/companies/{$this->company}";
    }

    protected function defaultBody(): array
    {
        return ['name' => $this->name];
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
