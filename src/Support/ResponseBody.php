<?php

declare(strict_types=1);

namespace Ecourier\Support;

use Ecourier\Exceptions\UnexpectedResponseException;
use Saloon\Http\Response;

/**
 * @internal
 */
final class ResponseBody
{
    /**
     * Decodes the JSON body of a successful response. Like Saloon, an empty body counts as an empty object.
     *
     * @return array<array-key, mixed>
     *
     * @throws UnexpectedResponseException When the body is not a JSON object, e.g. `null` or an HTML page.
     */
    public static function decode(Response $response): array
    {
        $body = json_decode($response->body() ?: '[]', true);

        if (! is_array($body)) {
            throw new UnexpectedResponseException('The eCourier response body is not a JSON object.');
        }

        return $body;
    }
}
