<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\DocumentListItemData;
use Saloon\Http\Request;
use Saloon\Http\Response;

class DocumentsPaginator extends EcourierPaginator
{
    protected function getPageItems(Response $response, Request $request): array
    {
        return array_map(
            fn(array $item) => DocumentListItemData::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}
