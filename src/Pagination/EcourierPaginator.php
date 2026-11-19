<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator;

/**
 * @template TItem of object
 */
abstract class EcourierPaginator extends PagedPaginator
{
    /**
     * Iterates over the items of every page.
     *
     * @return iterable<int, TItem>
     */
    public function items(): iterable
    {
        yield from parent::items();
    }

    /**
     * @return list<TItem>
     */
    abstract protected function getPageItems(Response $response, Request $request): array;

    protected function isLastPage(Response $response): bool
    {
        $currentPage = $response->json('meta.current_page');
        $lastPage = $response->json('meta.last_page');

        if (is_int($currentPage) && is_int($lastPage)) {
            return $currentPage >= $lastPage;
        }

        return $response->json('meta.next_page_url') === null;
    }
}
