<?php

declare(strict_types=1);

namespace Ecourier\Requests\Documents;

use BackedEnum;
use Ecourier\Enums\Channel;
use Ecourier\Enums\Direction;
use Ecourier\Enums\DocumentStatus;
use Ecourier\Enums\Sort;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetDocumentsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly DocumentStatus|array|null $status = null,
        private readonly Channel|array|null $channel = null,
        private readonly string|array|null $companyId = null,
        private readonly Direction|array|null $direction = null,
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
            'channel' => $this->values($this->channel),
            'company_id' => $this->values($this->companyId),
            'direction' => $this->values($this->direction),
            'status' => $this->values($this->status),
        ], fn($v) => $v !== null);

        if (!empty($filter)) {
            $query['filter'] = $filter;
        }

        return $query;
    }

    private function values(BackedEnum|string|array|null $values): ?array
    {
        if ($values === null || $values === []) {
            return null;
        }

        return array_map(
            fn(BackedEnum|string $value) => $value instanceof BackedEnum ? $value->value : $value,
            is_array($values) ? $values : [$values],
        );
    }
}
