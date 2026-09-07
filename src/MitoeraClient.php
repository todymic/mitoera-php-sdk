<?php

declare(strict_types=1);

namespace Mitoera\Sdk;

use Mitoera\Sdk\Client\ApiKeysClient;
use Mitoera\Sdk\Client\CategoriesClient;
use Mitoera\Sdk\Client\ChartsClient;
use Mitoera\Sdk\Client\EventsClient;
use Mitoera\Sdk\Client\HoldsClient;
use Mitoera\Sdk\Client\SessionsClient;
use Mitoera\Sdk\Client\WorkspacesClient;
use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\Http\HttpClient;

/**
 * Point d'entrée unique du SDK Mitoera.
 *
 * Les applications tierces n'instancient que cette classe.
 * Les sous-clients (HoldsClient, SessionsClient, EventsClient) sont des
 * détails d'implémentation — ils ne sont jamais instanciés par l'utilisateur.
 *
 * Formats de clé :
 *   keyId  → pk_live_xxxx  (production)  |  pk_test_xxxx  (sandbox)
 *   secret → sk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *
 * Le mode sandbox est auto-détecté depuis le préfixe de la clé.
 *
 * ── Usage ───────────────────────────────────────────────────────────────────
 *
 *   $client = new MitoeraClient(['keyId' => 'pk_live_xxxx', 'secret' => 'sk_xxx']);
 *
 *   $client->holds->hold($eventId, ['A1', 'A2'], $holdToken);
 *   $client->holds->book($eventId, ['A1', 'A2'], $holdToken);
 *   $client->holds->release($eventId, ['A1', 'A2'], $holdToken);
 *
 *   $session = $client->sessions->create($eventId);
 *
 *   $event = $client->events->get($eventId);
 */
class MitoeraClient
{
    public readonly HoldsClient       $holds;
    public readonly SessionsClient    $sessions;
    public readonly EventsClient      $events;
    public readonly ChartsClient      $charts;
    public readonly CategoriesClient  $categories;
    public readonly WorkspacesClient  $workspaces;
    public readonly ApiKeysClient     $apiKeys;

    /** @internal Exposed for HttpClient injection in tests only. */
    public readonly string $apiPrefix;

    private readonly HttpClient $http;
    private readonly string $credential;

    /**
     * @param array           $options  keyId, secret, [baseUrl], [mode], [timeout]
     * @param HttpClient|null $http     Inject a custom HTTP client (testing only)
     */
    public function __construct(array $options, ?HttpClient $http = null)
    {
        $keyId  = $options['keyId']  ?? null;
        $secret = $options['secret'] ?? null;

        if (!$keyId || !$secret) {
            throw new AuthException(
                'Both "keyId" (pk_live_… / pk_test_…) and "secret" (sk_…) are required.'
            );
        }

        $isSandbox = isset($options['mode'])
            ? $options['mode'] === 'sandbox'
            : str_starts_with($keyId, 'pk_test_');

        $this->apiPrefix  = $isSandbox ? '/sandbox-api' : '/api';
        $this->credential = $keyId . ':' . $secret;
        $this->http       = $http ?? new HttpClient(
            $options['baseUrl'] ?? 'https://api.mitoera.com',
            (int) ($options['timeout'] ?? 30),
        );

        $this->holds      = new HoldsClient($this);
        $this->sessions   = new SessionsClient($this);
        $this->events     = new EventsClient($this);
        $this->charts     = new ChartsClient($this);
        $this->categories = new CategoriesClient($this);
        $this->workspaces = new WorkspacesClient($this);
        $this->apiKeys    = new ApiKeysClient($this);
    }

    // ── HTTP verbs — used internally by sub-clients ──────────────────────────

    /** @internal */
    public function get(string $path): array
    {
        return $this->http->get($path, $this->authHeaders());
    }

    /** @internal */
    public function post(string $path, array $body = []): array
    {
        return $this->http->post($path, $body, $this->authHeaders());
    }

    /** @internal */
    public function put(string $path, array $body = []): array
    {
        return $this->http->put($path, $body, $this->authHeaders());
    }

    /** @internal */
    public function patch(string $path, array $body = []): array
    {
        return $this->http->patch($path, $body, $this->authHeaders());
    }

    /** @internal */
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
