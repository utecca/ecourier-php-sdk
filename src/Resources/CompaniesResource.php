<?php

declare(strict_types=1);

namespace Ecourier\Resources;

use Ecourier\Data\CompanyData;
use Ecourier\Requests\Companies\GetCompanyRequest;
use Saloon\Http\BaseResource;
use Saloon\Http\Response;

class CompaniesResource extends BaseResource
{
    public function get(string $company): Response
    {
        return $this->connector->send(new GetCompanyRequest($company));
    }

    public function find(string $company): CompanyData
    {
        return $this->get($company)->dto();
    }
}
