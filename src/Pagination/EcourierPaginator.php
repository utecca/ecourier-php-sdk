<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Exceptions\UnexpectedResponseException;
use Ecourier\Support\ResponseBody;
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

    /**
     * The raw items of a page, before they are turned into data objects.
     *
     * @return list<array<array-key, mixed>>
     */
    protected function pageData(Response $response): array
    {
        $data = ResponseBody::decode($response)['data'] ?? [];

        if (! is_array($data) || count(array_filter($data, is_array(...))) !== count($data)) {
            throw new UnexpectedResponseException('The eCourier response field "data" is not a list of objects.');
        }

        return array_values($data);
    }

    protected function isLastPage(Response $response): bool
    {
        $meta = ResponseBody::decode($response)['meta'] ?? null;
        $meta = is_array($meta) ? $meta : [];

        $currentPage = $meta['current_page'] ?? null;
        $lastPage = $meta['last_page'] ?? null;

        if (is_int($currentPage) && is_int($lastPage)) {
            return $currentPage >= $lastPage;
        }

        return ($meta['next_page_url'] ?? null) === null;
    }
}
