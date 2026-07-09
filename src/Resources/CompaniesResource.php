<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\CompanyData;
use Ecourier\Data\CreateCompanyData;
use Ecourier\Enums\Channel;
use Ecourier\Pagination\CompaniesPaginator;
use Ecourier\Requests\Companies\CreateCompanyRequest;
use Ecourier\Requests\Companies\DeleteCompanyRequest;
use Ecourier\Requests\Companies\GetCompaniesRequest;
use Ecourier\Requests\Companies\GetCompanyRequest;
use Ecourier\Requests\Companies\UpdateCompanyRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class CompaniesResource extends BaseResource
{
    public function list(
        Channel|array|null $channel = null,
        string|array|null $companyNo = null,
        string|array|null $country = null,
        string|array|null $name = null,
        ?bool $signed = null,
        int $perPage = 10,
    ): CompaniesPaginator {
        return new CompaniesPaginator($this->connector, new GetCompaniesRequest(
            channel: $channel,
            companyNo: $companyNo,
            country: $country,
            name: $name,
            signed: $signed,
            perPage: $perPage,
        ));
    }

    public function get(string $company): Response
    {
        return $this->connector->send(new GetCompanyRequest($company));
    }

    public function find(string $company): CompanyData
    {
        return $this->get($company)->dto();
    }

    public function create(CreateCompanyData $company): CompanyData
    {
        return $this->connector->send(new CreateCompanyRequest($company))->dto();
    }

    public function update(string $company, string $name): CompanyData
    {
        return $this->connector->send(new UpdateCompanyRequest($company, $name))->dto();
    }

    public function delete(string $company): Response
    {
        return $this->connector->send(new DeleteCompanyRequest($company));
    }
}
