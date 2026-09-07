<?php

declare(strict_types=1);

namespace Mitoera\Sdk;

use Mitoera\Sdk\Auth\TokenManager;
use Mitoera\Sdk\Client\EventsClient;
use Mitoera\Sdk\Client\HoldsClient;
use Mitoera\Sdk\Client\SessionsClient;
use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\Http\HttpClient;

/**
 * Main entry point for the Mitoera PHP SDK.
 *
 * ── BACKOFFICE key (sk_bo_xxx) ──────────────────────────────────────────────
 *   Full access: CRUD events, holds, sessions, bulk seat updates.
 *   The SDK exchanges keyId + secret for a short-lived JWT automatically and
 *   refreshes it before expiry — no manual token management needed.
 *
 *   $client = new MitoeraClient([
 *       'keyId'  => 'sk_bo_xxx',
 *       'secret' => '...',
 *   ]);
 *
 * ── PUBLIC key (sk_pub_xxx) ─────────────────────────────────────────────────
 *   Session generation and server-side holds only.
 *
 *   $client = new MitoeraClient(['publicKey' => 'sk_pub_xxx']);
 *
 * ── Options ─────────────────────────────────────────────────────────────────
 *   'baseUrl' => 'https://api.mitoera.com'   (default)
 *   'mode'    => 'production' | 'sandbox'     (sandbox prefixes /sandbox-api)
 *   'timeout' => 30                            (seconds)
 */
class MitoeraClient
{
    public readonly EventsClient $events;
    public readonly HoldsClient $holds;
    public readonly SessionsClient $sessions;

    private readonly HttpClient $http;
    private ?TokenManager $tokenManager = null;
    private ?string $publicKey = null;
    private readonly string $apiPrefix;

    public function __construct(array $options)
    {
        $baseUrl = rtrim($options['baseUrl'] ?? 'https://api.mitoera.com', '/');
        $timeout = (int) ($options['timeout'] ?? 30);
        $mode    = $options['mode'] ?? 'production';

        // Sandbox routes use a different URL prefix
        $isSandbox       = $mode === 'sandbox';
        $this->apiPrefix = $isSandbox ? '/sandbox-api' : '/api';

        $this->http = new HttpClient($baseUrl, $timeout);

        if (isset($options['keyId'], $options['secret'])) {
            $this->bootBackofficeKey($options['keyId'], $options['secret']);
        } elseif (isset($options['publicKey'])) {
            $this->bootPublicKey($options['publicKey']);
        } else {
            throw new AuthException(
                'Provide either [keyId + secret] for a BACKOFFICE key or [publicKey] for a PUBLIC key.'
            );
        }

        $authHeaders = $this->buildAuthHeadersClosure();

        $this->events   = new EventsClient($this->http, $authHeaders, $this->apiPrefix);
        $this->holds    = new HoldsClient($this->http, $authHeaders, $this->apiPrefix);
        $this->sessions = new SessionsClient($this->http, $authHeaders, $this->apiPrefix);
    }

    private function bootBackofficeKey(string $keyId, string $secret): void
    {
        $this->tokenManager = new TokenManager($this->http, $keyId, $secret);
    }

    private function bootPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * Returns a closure that always yields fresh auth headers.
     * Callers invoke it at request time so the JWT is never stale.
     */
    private function buildAuthHeadersClosure(): \Closure
    {
        if ($this->tokenManager !== null) {
            $manager = $this->tokenManager;

            return static function () use ($manager): array {
                return ['Authorization' => 'Bearer ' . $manager->bearerToken()];
            };
        }

        $key = $this->publicKey;

        return static function () use ($key): array {
            return ['Authorization' => 'Bearer ' . $key];
        };
    }
}
