<?php

declare(strict_types=1);

namespace Ecourier\Data\Responses\Webhook;

final class WebhookSignature
{
    public const HEADER = 'Signature';

    /**
     * Verify the Signature header of a webhook request against the raw, unparsed request body.
     */
    public static function verify(string $body, ?string $signature, string $secret): bool
    {
        if ($signature === null || $signature === '' || $secret === '') {
            return false;
        }

        return hash_equals(self::sign($body, $secret), $signature);
    }

    /**
     * Verify the current PHP request without passing the body or header yourself.
     *
     * By default, reads the raw body from `php://input` and the Signature header from `$_SERVER`.
     * If your framework has already consumed the request body (Laravel, Symfony, …), its request
     * object won't be able to re-read `php://input` — pass the body it gives you as `$body` instead.
     */
    public static function verifyCurrentRequest(string $secret, ?string $body = null, ?string $signature = null): bool
    {
        $body ??= file_get_contents('php://input') ?: '';
        $signature ??= $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', self::HEADER))] ?? null;

        return self::verify($body, $signature, $secret);
    }

    public static function sign(string $body, string $secret): string
    {
        return hash_hmac('sha256', $body, $secret);
    }
}
