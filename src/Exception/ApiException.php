<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Exception;

class ApiException extends MitoeraException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?array $body = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Build the exception for an error response.
     *
     * 401 and 403 return an AuthException so callers can single out
     * authentication failures. Both remain catchable as ApiException.
     *
     * @param  string|null $request  "GET https://api.mitoera.com/api/charts",
     *                               used to name the base URL actually hit
     * @param  string|null $keyHint  Key prefix in use ("pk_test_"), never the
     *                               full keyId and never the secret
     */
    public static function fromResponse(
        int $status,
        ?array $body,
        ?string $request = null,
        ?string $keyHint = null,
    ): self {
        $detail = $body['message'] ?? $body['detail'] ?? $body['error'] ?? null;
        $detail = is_string($detail) && $detail !== '' ? $detail : null;

        if ($status === 401 || $status === 403) {
            return new AuthException(
                self::authMessage($status, $detail, $request, $keyHint),
                $status,
                $body,
            );
        }

        $message = $detail ?? "HTTP $status";

        if ($detail === null && $request !== null) {
            $message .= " on $request";
        }

        return new self($message, $status, $body);
    }

    /**
     * Spell out what a bare 401 actually means.
     *
     * The API often answers an expired key with an empty body, which used to
     * surface as the literal string "HTTP 401" — indistinguishable from a
     * missing header or a wrong base URL, and the single costliest thing to
     * diagnose for integrators.
     */
    private static function authMessage(
        int $status,
        ?string $detail,
        ?string $request,
        ?string $keyHint,
    ): string {
        $parts = [];

        $parts[] = $detail !== null
            ? "Authentication rejected (HTTP $status): $detail"
            : "Authentication rejected (HTTP $status) — the API returned no error detail.";

        if ($request !== null) {
            $parts[] = "Request: $request.";
        }

        if ($keyHint !== null) {
            $mode = match (true) {
                str_starts_with($keyHint, 'pk_test_') => ' (sandbox)',
                str_starts_with($keyHint, 'pk_live_') => ' (production)',
                default                               => '',
            };
            $parts[] = "Key in use: {$keyHint}…{$mode}.";
        }

        $parts[] = 'The API was reached and refused the credentials. Likely causes: '
            . 'the key was revoked or has expired; keyId and secret come from different '
            . 'key pairs; the key belongs to another instance than the one baseUrl points at. '
            . "A wrong baseUrl produces a 404 or a connection error, never a $status.";

        return implode(' ', $parts);
    }
}
