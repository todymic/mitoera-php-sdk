<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\SessionResponse;

class SessionsClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /**
     * Create a session token to pass to the browser widget.
     */
    public function create(string $eventId): SessionResponse
    {
        $data = $this->client->post(
            "{$this->client->apiPrefix}/public/sessions",
            ['eventId' => $eventId],
        );

        return SessionResponse::fromArray($data);
    }

    /**
     * Refresh an expiring session token.
     */
    public function refresh(string $sessionToken): SessionResponse
    {
        $data = $this->client->post(
            "{$this->client->apiPrefix}/public/sessions/refresh",
            ['sessionToken' => $sessionToken],
        );

        return SessionResponse::fromArray($data);
    }
}
