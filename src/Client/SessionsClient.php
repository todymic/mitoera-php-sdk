<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\Response\SessionResponse;

class SessionsClient
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly \Closure $authHeaders,
        private readonly string $apiPrefix,
    ) {}

    /**
     * Create a session token to pass to the browser widget.
     *
     * Uses POST /api/public/sessions (requires sk_pub_xxx key).
     */
    public function create(string $eventId): SessionResponse
    {
        $data = $this->http->post(
            "{$this->apiPrefix}/public/sessions",
            ['eventId' => $eventId],
            ($this->authHeaders)(),
        );

        return SessionResponse::fromArray($data);
    }

    /**
     * Refresh an expiring session token.
     */
    public function refresh(string $sessionToken): SessionResponse
    {
        $data = $this->http->post(
            "{$this->apiPrefix}/public/sessions/refresh",
            ['sessionToken' => $sessionToken],
            ($this->authHeaders)(),
        );

        return SessionResponse::fromArray($data);
    }
}
