<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\ApiKeysClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\ApiKeyCreatedResponse;
use Mitoera\Sdk\Response\ApiKeyResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiKeysClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private ApiKeysClient $apiKeys;

    protected function setUp(): void
    {
        $this->http    = $this->createMock(HttpClient::class);
        $core          = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->apiKeys = new ApiKeysClient($core);
    }

    public function test_list_all_returns_array_of_api_key_responses(): void
    {
        $this->http->expects($this->once())
            ->method('get')
            ->with('/api/api-keys', $this->anything())
            ->willReturn([
                ['id' => 'key-1', 'name' => 'CI key', 'keyId' => 'pk_live_ci', 'scope' => 'PUBLIC', 'active' => true, 'createdAt' => null, 'lastUsedAt' => null],
            ]);

        $result = $this->apiKeys->listAll();

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ApiKeyResponse::class, $result[0]);
        $this->assertSame('CI key', $result[0]->name);
    }

    public function test_create_posts_name_and_scope_and_returns_created_response(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/api-keys', ['name' => 'Tapakila server', 'scope' => 'PUBLIC'], $this->anything())
            ->willReturn([
                'id'        => 'key-2',
                'name'      => 'Tapakila server',
                'keyId'     => 'pk_live_abc',
                'secret'    => 'sk_supersecret',
                'scope'     => 'PUBLIC',
                'createdAt' => '2025-01-01T00:00:00+00:00',
            ]);

        $result = $this->apiKeys->create('Tapakila server');

        $this->assertInstanceOf(ApiKeyCreatedResponse::class, $result);
        $this->assertSame('sk_supersecret', $result->secret);
    }

    public function test_delete_calls_delete_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('delete')
            ->with('/api/api-keys/key-1', $this->anything())
            ->willReturn([]);

        $this->apiKeys->delete('key-1');
    }
}
