<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Exception;

/**
 * Raised when credentials are missing, malformed, or refused by the API.
 *
 * Two situations produce it:
 *
 *   - a configuration error caught before any request leaves the process
 *     (missing keyId or secret) — `statusCode` is then 0;
 *   - an HTTP 401 or 403 returned by the API — `statusCode` carries it.
 *
 * It extends ApiException so that code already written as
 * `catch (ApiException $e)` keeps catching authentication failures, which is
 * what every 4xx did before this class was specialised.
 */
class AuthException extends ApiException
{
    public function __construct(
        string $message,
        int $statusCode = 0,
        ?array $body = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $body, $previous);
    }
}
