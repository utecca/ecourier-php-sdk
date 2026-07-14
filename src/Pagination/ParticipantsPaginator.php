<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\ParticipantData;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator;

class ParticipantsPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return is_null($response->json('links.next'));
    }

    protected function getPageItems(Response $response, Request $request): array
    {
        return array_map(
            fn(array $item) => ParticipantData::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}
