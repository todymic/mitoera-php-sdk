<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\ChartsClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\ChartResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChartsClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private ChartsClient $charts;

    private array $chartPayload = [
        'id'                => 'chart-1',
        'name'              => 'Salle Zénith',
        'objects'           => [],
        'updatedAt'         => '2025-01-01T00:00:00+00:00',
        'status'            => 'draft',
        'pendingChanges'    => false,
        'publishedSnapshot' => null,
    ];

    protected function setUp(): void
    {
        $this->http   = $this->createMock(HttpClient::class);
        $core         = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->charts = new ChartsClient($core);
    }

    public function test_get_fetches_by_id(): void
    {
        $this->http->expects($this->once())
            ->method('get')
            ->with('/api/charts/chart-1', $this->anything())
            ->willReturn($this->chartPayload);

        $result = $this->charts->get('chart-1');

        $this->assertInstanceOf(ChartResponse::class, $result);
        $this->assertSame('chart-1', $result->id);
        $this->assertSame('Salle Zénith', $result->name);
        $this->assertTrue($result->isDraft());
    }

    public function test_create_posts_name(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/charts', ['name' => 'Nouveau plan'], $this->anything())
            ->willReturn($this->chartPayload);

        $result = $this->charts->create('Nouveau plan');
        $this->assertInstanceOf(ChartResponse::class, $result);
    }

    public function test_publish_posts_to_publish_endpoint(): void
    {
        $published = array_merge($this->chartPayload, ['status' => 'published']);

        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/charts/chart-1/publish', [], $this->anything())
            ->willReturn($published);

        $result = $this->charts->publish('chart-1');
        $this->assertTrue($result->isPublished());
    }

    public function test_mark_pending_posts_to_mark_pending_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/charts/chart-1/mark-pending', [], $this->anything())
            ->willReturn(array_merge($this->chartPayload, ['pendingChanges' => true]));

        $result = $this->charts->markPending('chart-1');
        $this->assertTrue($result->pendingChanges);
    }

    public function test_delete_calls_delete_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('delete')
            ->with('/api/charts/chart-1', $this->anything())
            ->willReturn([]);

        $this->charts->delete('chart-1');
    }

    public function test_set_objects_puts_to_objects_endpoint(): void
    {
        $objects = [['type' => 'seat', 'key' => 'A1']];

        $this->http->expects($this->once())
            ->method('put')
            ->with('/api/charts/chart-1/objects', ['objects' => $objects], $this->anything())
            ->willReturn(array_merge($this->chartPayload, ['objects' => $objects]));

        $result = $this->charts->setObjects('chart-1', $objects);
        $this->assertCount(1, $result->objects);
    }

    public function test_sandbox_client_uses_sandbox_prefix(): void
    {
        $http    = $this->createMock(HttpClient::class);
        $sandbox = new MitoeraClient(['keyId' => 'pk_test_abc', 'secret' => 'sk_xxx'], $http);
        $charts  = new ChartsClient($sandbox);

        $http->expects($this->once())
            ->method('get')
            ->with('/sandbox-api/charts/chart-1', $this->anything())
            ->willReturn($this->chartPayload);

        $charts->get('chart-1');
    }
}
