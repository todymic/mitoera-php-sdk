<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\SessionsClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\SessionResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SessionsClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private SessionsClient $sessions;

    private array $sessionPayload = [
        'sessionToken' => 'sess-abc',
        'holdToken'    => 'hold-xyz',
        'eventId'      => 'event-1',
        'expiresIn'    => 600,
    ];

    protected function setUp(): void
    {
        $this->http     = $this->createMock(HttpClient::class);
        $core           = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->sessions = new SessionsClient($core);
    }

    public function test_create_posts_to_public_sessions(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/public/sessions', ['eventId' => 'event-1'], $this->anything())
            ->willReturn($this->sessionPayload);

        $result = $this->sessions->create('event-1');

        $this->assertInstanceOf(SessionResponse::class, $result);
        $this->assertSame('sess-abc', $result->sessionToken);
        $this->assertSame('hold-xyz', $result->holdToken);
    }

    public function test_refresh_posts_to_refresh_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/public/sessions/refresh', ['sessionToken' => 'sess-old'], $this->anything())
            ->willReturn($this->sessionPayload);

        $result = $this->sessions->refresh('sess-old');
        $this->assertSame('sess-abc', $result->sessionToken);
    }

    public function test_sandbox_core_routes_to_sandbox_prefix(): void
    {
        $http     = $this->createMock(HttpClient::class);
        $sandbox  = new MitoeraClient(['keyId' => 'pk_test_abc', 'secret' => 'sk_xxx'], $http);
        $sessions = new SessionsClient($sandbox);

        $http->expects($this->once())
            ->method('post')
            ->with('/sandbox-api/public/sessions', $this->anything(), $this->anything())
            ->willReturn($this->sessionPayload);

        $sessions->create('event-1');
    }
}
