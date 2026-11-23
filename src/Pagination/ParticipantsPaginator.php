<?php

declare(strict_types=1);

namespace Ecourier\Pagination;

use Ecourier\Data\Responses\ParticipantData;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * @extends EcourierPaginator<ParticipantData>
 */
class ParticipantsPaginator extends EcourierPaginator
{
    /**
     * @return list<ParticipantData>
     */
    protected function getPageItems(Response $response, Request $request): array
    {
        return array_map(
            fn(array $item) => ParticipantData::fromArray($item),
            $this->pageData($response),
        );
    }
}
