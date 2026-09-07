<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\SessionsClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\Response\SessionResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionsClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private SessionsClient $client;

    private array $sessionPayload = [
        'sessionToken' => 'sess-abc',
        'holdToken'    => 'hold-xyz',
        'eventId'      => 'event-1',
        'expiresIn'    => 600,
    ];

    protected function setUp(): void
    {
        $this->http   = $this->createMock(HttpClient::class);
        $authHeaders  = static fn() => ['Authorization' => 'Bearer sk_pub_test'];
        $this->client = new SessionsClient($this->http, $authHeaders, '/api');
    }

    public function test_create_returns_session_response(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/public/sessions', ['eventId' => 'event-1'], $this->anything())
            ->willReturn($this->sessionPayload);

        $result = $this->client->create('event-1');

        $this->assertInstanceOf(SessionResponse::class, $result);
        $this->assertSame('sess-abc', $result->sessionToken);
        $this->assertSame('hold-xyz', $result->holdToken);
        $this->assertSame(600, $result->expiresIn);
    }

    public function test_refresh_sends_existing_token(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/public/sessions/refresh', ['sessionToken' => 'sess-old'], $this->anything())
            ->willReturn($this->sessionPayload);

        $result = $this->client->refresh('sess-old');

        $this->assertInstanceOf(SessionResponse::class, $result);
        $this->assertSame('sess-abc', $result->sessionToken);
    }

    public function test_create_uses_sandbox_prefix(): void
    {
        $authHeaders   = static fn() => [];
        $sandboxClient = new SessionsClient($this->http, $authHeaders, '/sandbox-api');

        $this->http->expects($this->once())
            ->method('post')
            ->with('/sandbox-api/public/sessions', $this->anything(), $this->anything())
            ->willReturn($this->sessionPayload);

        $sandboxClient->create('event-1');
    }
}
