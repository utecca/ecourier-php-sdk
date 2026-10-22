<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\CompanyListItemData;
use Saloon\Http\Request;
use Saloon\Http\Response;

class CompaniesPaginator extends EcourierPaginator
{
    protected function getPageItems(Response $response, Request $request): array
    {
        return array_map(
            fn(array $item) => CompanyListItemData::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}
