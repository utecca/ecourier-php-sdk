<?php

declare(strict_types=1);

namespace Ecourier;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Throwable;

/**
 * A response whose request no longer holds the API key, so dumps, logs and error trackers can show it in full.
 *
 * The request has been sent when the response is created, so the key is masked to something like "pk_live_…a1b2".
 */
class EcourierResponse extends Response
{
    public function __construct(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, ?Throwable $senderException = null)
    {
        if ($psrRequest->hasHeader('Authorization')) {
            $psrRequest = $psrRequest->withHeader(
                'Authorization',
                array_map(self::maskAuthorization(...), $psrRequest->getHeader('Authorization')),
            );
        }

        $authenticator = $pendingRequest->getAuthenticator();

        if ($authenticator instanceof TokenAuthenticator) {
            $pendingRequest->authenticate(new TokenAuthenticator(self::maskToken($authenticator->token), $authenticator->prefix));
        }

        $authorization = $pendingRequest->headers()->get('Authorization');

        if (is_string($authorization)) {
            $pendingRequest->headers()->add('Authorization', self::maskAuthorization($authorization));
        }

        parent::__construct($psrResponse, $pendingRequest, $psrRequest, $senderException);
    }

    private static function maskAuthorization(string $value): string
    {
        $parts = explode(' ', $value, 2);

        return count($parts) === 2
            ? $parts[0] . ' ' . self::maskToken($parts[1])
            : self::maskToken($value);
    }

    /**
     * Keeps the "pk_test_" or "pk_live_" prefix and the last four characters. Masking a masked token changes nothing.
     */
    private static function maskToken(string $token): string
    {
        if (str_contains($token, '…')) {
            return $token;
        }

        $prefix = preg_match('/^[a-z]+_[a-z]+_/', $token, $matches) === 1 ? $matches[0] : '';
        $rest = substr($token, strlen($prefix));

        return $prefix . '…' . (strlen($rest) > 8 ? substr($rest, -4) : '');
    }
}
