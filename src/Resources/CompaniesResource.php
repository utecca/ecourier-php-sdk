<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\Responses\CompanyData;
use Ecourier\Data\Requests\CreateCompanyData;
use Ecourier\Data\Requests\SubmitAuthorisationPdfData;
use Ecourier\Data\Requests\UpdateCompanyData;
use Ecourier\Enums\Channel;
use Ecourier\Pagination\CompaniesPaginator;
use Ecourier\Requests\Companies\CreateCompanyRequest;
use Ecourier\Requests\Companies\DeleteCompanyRequest;
use Ecourier\Requests\Companies\GetCompaniesRequest;
use Ecourier\Requests\Companies\GetCompanyRequest;
use Ecourier\Requests\Companies\SubmitAuthorisationPdfRequest;
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
        int $perPage = 25,
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

    public function update(string $company, UpdateCompanyData $data): CompanyData
    {
        return $this->connector->send(new UpdateCompanyRequest($company, $data))->dto();
    }

    /**
     * Upload a signed authorisation PDF for the company. Only available to teams with a Custom signing flow.
     */
    public function submitAuthorisationPdf(string $company, SubmitAuthorisationPdfData $data): CompanyData
    {
        return $this->connector->send(new SubmitAuthorisationPdfRequest($company, $data))->dto();
    }

    public function delete(string $company): Response
    {
        return $this->connector->send(new DeleteCompanyRequest($company));
    }
}
