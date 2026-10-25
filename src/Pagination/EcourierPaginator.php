<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator;

abstract class EcourierPaginator extends PagedPaginator
{
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
