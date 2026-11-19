<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\Responses\CompanyListItemData;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * @extends EcourierPaginator<CompanyListItemData>
 */
class CompaniesPaginator extends EcourierPaginator
{
    /**
     * @return list<CompanyListItemData>
     */
    protected function getPageItems(Response $response, Request $request): array
    {
        return array_values(array_map(
            fn(array $item) => CompanyListItemData::fromArray($item),
            $response->json('data') ?? [],
        ));
    }
}
