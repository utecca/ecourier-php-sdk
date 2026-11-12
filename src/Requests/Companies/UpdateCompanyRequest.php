<?php

declare(strict_types=1);

namespace Ecourier\Requests\Companies;

use Ecourier\Data\Requests\UpdateCompanyData;
use Ecourier\Data\Responses\CompanyData;
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
        private readonly UpdateCompanyData $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/companies/{$this->company}";
    }

    protected function defaultBody(): array
    {
        return ['name' => $this->data->name];
    }

    public function createDtoFromResponse(Response $response): CompanyData
    {
        return CompanyData::fromArray($response->array());
    }
}
