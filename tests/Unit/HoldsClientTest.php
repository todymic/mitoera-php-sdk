<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\HoldsClient;
use Mitoera\Sdk\Exception\ApiException;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\Response\BookResponse;
use Mitoera\Sdk\Response\HoldResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HoldsClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private HoldsClient $client;

    protected function setUp(): void
    {
        $this->http   = $this->createMock(HttpClient::class);
        $authHeaders  = static fn() => ['Authorization' => 'Bearer test-jwt'];
        $this->client = new HoldsClient($this->http, $authHeaders, '/api');
    }

    public function test_hold_returns_hold_response(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with(
                '/api/events/event-1/hold',
                ['seatKeys' => ['A1', 'A2'], 'holdToken' => 'ht-123'],
                ['Authorization' => 'Bearer test-jwt'],
            )
            ->willReturn([
                'holdToken'       => 'ht-123',
                'seatKeys'        => ['A1', 'A2'],
                'expiresAt'       => '2025-12-31T12:10:00+00:00',
                'durationSeconds' => 600,
            ]);

        $result = $this->client->hold('event-1', ['A1', 'A2'], 'ht-123');

        $this->assertInstanceOf(HoldResponse::class, $result);
        $this->assertSame('ht-123', $result->holdToken);
        $this->assertSame(['A1', 'A2'], $result->seatKeys);
        $this->assertSame(600, $result->durationSeconds);
    }

    public function test_book_returns_book_response(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/events/event-1/book', $this->anything(), $this->anything())
            ->willReturn([
                'bookedSeats' => ['A1', 'A2'],
                'eventId'     => 'event-1',
                'bookedAt'    => '2025-12-31T12:00:00+00:00',
            ]);

        $result = $this->client->book('event-1', ['A1', 'A2'], 'ht-123');

        $this->assertInstanceOf(BookResponse::class, $result);
        $this->assertSame(['A1', 'A2'], $result->bookedSeats);
        $this->assertSame('event-1', $result->eventId);
    }

    public function test_release_calls_correct_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/events/event-1/release', ['seatKeys' => ['A1'], 'holdToken' => 'ht-123'], $this->anything());

        $this->client->release('event-1', ['A1'], 'ht-123');
    }

    public function test_hold_propagates_api_exception(): void
    {
        $this->http->method('post')
            ->willThrowException(ApiException::fromResponse(409, ['message' => 'Seat already held']));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Seat already held');
        $this->expectExceptionCode(409);

        $this->client->hold('event-1', ['A1'], 'ht-xxx');
    }

    public function test_hold_uses_sandbox_prefix(): void
    {
        $authHeaders  = static fn() => ['Authorization' => 'Bearer test-jwt'];
        $sandboxClient = new HoldsClient($this->http, $authHeaders, '/sandbox-api');

        $this->http->expects($this->once())
            ->method('post')
            ->with('/sandbox-api/events/event-1/hold', $this->anything(), $this->anything())
            ->willReturn([
                'holdToken' => 'ht-1', 'seatKeys' => ['A1'],
                'expiresAt' => '2025-12-31T12:00:00+00:00', 'durationSeconds' => 600,
            ]);

        $sandboxClient->hold('event-1', ['A1'], 'ht-1');
    }
}
