<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\ApiKeyCreatedResponse;
use Mitoera\Sdk\Response\ApiKeyResponse;

/** @internal Accessed via $client->apiKeys */
class ApiKeysClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /** @return ApiKeyResponse[] */
    public function listAll(): array
    {
        $data = $this->client->get($this->client->apiPrefix . '/api-keys');
        return array_map(ApiKeyResponse::fromArray(...), $data['items'] ?? $data);
    }

    /**
     * @param string $scope 'BACKOFFICE' | 'PUBLIC'
     */
    public function create(string $name, string $scope = 'PUBLIC'): ApiKeyCreatedResponse
    {
        return ApiKeyCreatedResponse::fromArray(
            $this->client->post(
                $this->client->apiPrefix . '/api-keys',
                ['name' => $name, 'scope' => $scope],
            )
        );
    }

    public function delete(string $apiKeyId): void
    {
        $this->client->delete($this->client->apiPrefix . '/api-keys/' . $apiKeyId);
    }
}
