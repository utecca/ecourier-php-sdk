<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\Responses\DocumentListItemData;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * @extends EcourierPaginator<DocumentListItemData>
 */
class DocumentsPaginator extends EcourierPaginator
{
    /**
     * @return list<DocumentListItemData>
     */
    protected function getPageItems(Response $response, Request $request): array
    {
        return array_values(array_map(
            fn(array $item) => DocumentListItemData::fromArray($item),
            $response->json('data') ?? [],
        ));
    }
}
