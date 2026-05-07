<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Data;

class PaginatedData
{
    public function __construct(
        public readonly array $data,
        public readonly int $total,
        public readonly int $perPage,
        public readonly int $currentPage,
        public readonly int $lastPage,
        public readonly ?string $nextPageUrl,
        public readonly ?string $prevPageUrl,
    ) {}

    public static function fromArray(array $response): self
    {
        $meta = $response['meta'] ?? $response;

        return new self(
            data: $response['data'] ?? [],
            total: $meta['total'] ?? 0,
            perPage: $meta['per_page'] ?? 25,
            currentPage: $meta['current_page'] ?? 1,
            lastPage: $meta['last_page'] ?? 1,
            nextPageUrl: $response['links']['next'] ?? $meta['next_page_url'] ?? null,
            prevPageUrl: $response['links']['prev'] ?? $meta['prev_page_url'] ?? null,
        );
    }
}
