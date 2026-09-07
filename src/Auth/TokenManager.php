<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Auth;

use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\Http\HttpClient;

/**
 * Manages JWT lifecycle for BACKOFFICE keys (sk_bo_xxx).
 *
 * Calls POST /api/auth/embed-token to exchange the API key pair for a short-lived
 * JWT, then transparently refreshes it before it expires.
 */
class TokenManager
{
    private ?string $jwt = null;
    private int $expiresAt = 0;

    /** Refresh 60 s before actual expiry to avoid races. */
    private const MARGIN_SECONDS = 60;

    public function __construct(
        private readonly HttpClient $http,
        private readonly string $keyId,
        private readonly string $secret,
    ) {}

    /**
     * Returns a valid Bearer JWT, fetching a fresh one if needed.
     */
    public function bearerToken(): string
    {
        if ($this->jwt === null || time() >= $this->expiresAt) {
            $this->refresh();
        }

        return $this->jwt;
    }

    private function refresh(): void
    {
        $data = $this->http->post('/api/auth/embed-token', [
            'keyId'  => $this->keyId,
            'secret' => $this->secret,
        ]);

        if (empty($data['token'])) {
            throw new AuthException('embed-token response missing "token" field');
        }

        $this->jwt = $data['token'];

        // Decode the JWT payload to read the `exp` claim.
        $this->expiresAt = $this->extractExp($this->jwt) - self::MARGIN_SECONDS;
    }

    private function extractExp(string $jwt): int
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            // Fallback: assume 1-hour expiry if the token is opaque.
            return time() + 3600;
        }

        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

        return $payload['exp'] ?? time() + 3600;
    }
}
