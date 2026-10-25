<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\ParticipantData;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ParticipantsPaginator extends EcourierPaginator
{
    protected function getPageItems(Response $response, Request $request): array
    {
        return array_map(
            fn(array $item) => ParticipantData::fromArray($item),
            $response->json('data') ?? [],
        );
    }
}
