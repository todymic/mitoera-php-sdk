<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\HoldsClient;
use Mitoera\Sdk\Exception\ApiException;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\BookResponse;
use Mitoera\Sdk\Response\HoldResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HoldsClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private MitoeraClient $core;
    private HoldsClient $holds;

    protected function setUp(): void
    {
        $this->http  = $this->createMock(HttpClient::class);
        $this->core  = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->holds = new HoldsClient($this->core);
    }

    public function test_hold_posts_to_correct_path_and_returns_response(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/events/event-1/hold', ['seatKeys' => ['A1', 'A2'], 'holdToken' => 'ht-1'], $this->anything())
            ->willReturn([
                'holdToken' => 'ht-1', 'seatKeys' => ['A1', 'A2'],
                'expiresAt' => '2025-12-31T12:10:00+00:00', 'durationSeconds' => 600,
            ]);

        $result = $this->holds->hold('event-1', ['A1', 'A2'], 'ht-1');

        $this->assertInstanceOf(HoldResponse::class, $result);
        $this->assertSame('ht-1', $result->holdToken);
        $this->assertSame(600, $result->durationSeconds);
    }

    public function test_book_returns_book_response(): void
    {
        $this->http->method('post')->willReturn([
            'bookedSeats' => ['A1', 'A2'],
            'eventId'     => 'event-1',
            'bookedAt'    => '2025-12-31T12:00:00+00:00',
        ]);

        $result = $this->holds->book('event-1', ['A1', 'A2'], 'ht-1');

        $this->assertInstanceOf(BookResponse::class, $result);
        $this->assertSame(['A1', 'A2'], $result->bookedSeats);
    }

    public function test_release_calls_correct_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/events/event-1/release', ['seatKeys' => ['A1'], 'holdToken' => 'ht-1'], $this->anything());

        $this->holds->release('event-1', ['A1'], 'ht-1');
    }

    public function test_hold_propagates_api_exception(): void
    {
        $this->http->method('post')
            ->willThrowException(ApiException::fromResponse(409, ['message' => 'Seat already held']));

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(409);

        $this->holds->hold('event-1', ['A1'], 'ht-1');
    }

    public function test_sandbox_core_uses_sandbox_prefix(): void
    {
        $http     = $this->createMock(HttpClient::class);
        $sandbox  = new MitoeraClient(['keyId' => 'pk_test_abc', 'secret' => 'sk_xxx'], $http);
        $holds    = new HoldsClient($sandbox);

        $http->expects($this->once())
            ->method('post')
            ->with('/api/events/event-1/hold', $this->anything(), $this->anything())
            ->willReturn([
                'holdToken' => 'ht-1', 'seatKeys' => ['A1'],
                'expiresAt' => '2025-12-31T12:00:00+00:00', 'durationSeconds' => 600,
            ]);

        $holds->hold('event-1', ['A1'], 'ht-1');
    }
}
