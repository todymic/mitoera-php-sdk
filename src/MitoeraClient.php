<?php

declare(strict_types=1);

namespace Mitoera\Sdk;

use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\Http\HttpClient;

/**
 * HTTP + auth core. Domain clients (HoldsClient, SessionsClient, …) depend on
 * this class, not the other way around.
 *
 * Key format (current Mitoera):
 *   keyId  → pk_live_xxxx  (production)  |  pk_test_xxxx  (sandbox)
 *   secret → sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * Sandbox is auto-detected from the key prefix. Override with 'mode' if needed.
 *
 * Auth header sent on every request: Authorization: ApiKey {keyId}:{secret}
 *
 * ── Usage ───────────────────────────────────────────────────────────────────
 *
 *   $client = new MitoeraClient(['keyId' => 'pk_live_xxxx', 'secret' => 'sk_xxx']);
 *
 *   // Then instantiate only the domain client you need:
 *   $holds    = new HoldsClient($client);
 *   $sessions = new SessionsClient($client);
 *   $events   = new EventsClient($client);
 */
class MitoeraClient
{
    /** /api  or  /sandbox-api — read by domain clients to prefix their routes. */
    public readonly string $apiPrefix;

    private readonly HttpClient $http;
    private readonly string $credential;

    /**
     * @param  array       $options  keyId, secret, [baseUrl], [mode], [timeout]
     * @param  HttpClient|null $http  Inject a custom HTTP client (useful for testing)
     */
    public function __construct(array $options, ?HttpClient $http = null)
    {
        $keyId  = $options['keyId']  ?? null;
        $secret = $options['secret'] ?? null;

        if (!$keyId || !$secret) {
            throw new AuthException('Both "keyId" (pk_live_… / pk_test_…) and "secret" (sk_…) are required.');
        }

        // Sandbox auto-detection: pk_test_ prefix → sandbox, pk_live_ → production.
        // An explicit 'mode' option overrides the auto-detection.
        $isSandbox = isset($options['mode'])
            ? $options['mode'] === 'sandbox'
            : str_starts_with($keyId, 'pk_test_');

        $this->apiPrefix  = $isSandbox ? '/sandbox-api' : '/api';
        $this->credential = $keyId . ':' . $secret;
        $this->http       = $http ?? new HttpClient(
            $options['baseUrl'] ?? 'https://api.mitoera.com',
            (int) ($options['timeout'] ?? 30),
        );
    }

    // ── HTTP verbs ───────────────────────────────────────────────────────────

    public function get(string $path): array
    {
        return $this->http->get($path, $this->authHeaders());
    }

    public function post(string $path, array $body = []): array
    {
        return $this->http->post($path, $body, $this->authHeaders());
    }

    public function put(string $path, array $body = []): array
    {
        return $this->http->put($path, $body, $this->authHeaders());
    }

    public function patch(string $path, array $body = []): array
    {
        return $this->http->patch($path, $body, $this->authHeaders());
    }

    public function delete(string $path): array
    {
        return $this->http->delete($path, $this->authHeaders());
    }

    // ────────────────────────────────────────────────────────────────────────

    private function authHeaders(): array
    {
        return ['Authorization' => 'ApiKey ' . $this->credential];
    }
}
