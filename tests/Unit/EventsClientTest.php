<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\EventsClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\EventResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EventsClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private EventsClient $events;

    protected function setUp(): void
    {
        $this->http   = $this->createMock(HttpClient::class);
        $core         = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->events = new EventsClient($core);
    }

    public function test_get_fetches_full_event(): void
    {
        $this->http->expects($this->once())
            ->method('get')
            ->with('/api/events/event-1', $this->anything())
            ->willReturn([
                'id'         => 'event-1',
                'title'      => 'Concert Zénith',
                'identifier' => 'concert-zenith',
                'chartId'    => 'chart-1',
            ]);

        $result = $this->events->get('event-1');

        $this->assertSame('event-1', $result->id);
        $this->assertSame('Concert Zénith', $result->title);
        $this->assertSame('concert-zenith', $result->identifier);
    }

    /**
     * /events/lookup/{identifier} returns a minimal {"id": "..."} payload,
     * not the full event resource — findByIdentifier() must not crash on
     * the missing title/identifier fields.
     */
    public function test_find_by_identifier_tolerates_minimal_lookup_payload(): void
    {
        $this->http->expects($this->once())
            ->method('get')
            ->with('/api/events/lookup/some-reference', $this->anything())
            ->willReturn(['id' => 'event-1']);

        $result = $this->events->findByIdentifier('some-reference');

        $this->assertInstanceOf(EventResponse::class, $result);
        $this->assertSame('event-1', $result->id);
        $this->assertSame('', $result->title);
        $this->assertSame('', $result->identifier);
    }
}
