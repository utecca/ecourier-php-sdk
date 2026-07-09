<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Pagination;

use Ecourier\Sdk\Data\DocumentData;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator;

class DocumentsPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return is_null($response->json('links.next'));
    }

    protected function getPageItems(Response $response, Request $request): array
    {
        return array_map(
            fn(array $item) => DocumentData::fromArray($item),
            $response->json('data') ?? [],
        );
    }

}
