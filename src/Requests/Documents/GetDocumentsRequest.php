<?php

declare(strict_types=1);

namespace Ecourier\Sdk\Requests\Documents;

use Ecourier\Sdk\Enums\DocumentStatus;
use Ecourier\Sdk\Enums\Sort;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetDocumentsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?DocumentStatus $status = null,
        private readonly ?string $createdAt = null,
        private readonly ?string $identityId = null,
        private readonly ?Sort $sort = null,
        private readonly int $perPage = 10,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/documents';
    }

    protected function defaultQuery(): array
    {
        $query = ['per_page' => $this->perPage];

        if ($this->sort !== null) {
            $query['sort'] = $this->sort->value;
        }

        $filter = array_filter([
            'status' => $this->status?->value,
            'created_at' => $this->createdAt,
            'identity_id' => $this->identityId,
        ], fn($v) => $v !== null);

        if (!empty($filter)) {
            $query['filter'] = $filter;
        }

        return $query;
    }
}
