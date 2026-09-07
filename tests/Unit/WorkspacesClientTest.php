<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\WorkspacesClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\WorkspaceResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WorkspacesClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private WorkspacesClient $workspaces;

    private array $wsPayload = [
        'id'        => 'ws-1',
        'name'      => 'Ticketevent',
        'slug'      => 'ticketevent',
        'current'   => true,
        'createdAt' => '2024-06-01T00:00:00+00:00',
    ];

    protected function setUp(): void
    {
        $this->http       = $this->createMock(HttpClient::class);
        $core             = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->workspaces = new WorkspacesClient($core);
    }

    public function test_get_current_returns_workspace_response(): void
    {
        $this->http->expects($this->once())
            ->method('get')
            ->with('/api/workspaces/current', $this->anything())
            ->willReturn($this->wsPayload);

        $result = $this->workspaces->getCurrent();

        $this->assertInstanceOf(WorkspaceResponse::class, $result);
        $this->assertTrue($result->current);
        $this->assertSame('ticketevent', $result->slug);
    }

    public function test_create_posts_name(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/workspaces', ['name' => 'Tapakila'], $this->anything())
            ->willReturn(array_merge($this->wsPayload, ['name' => 'Tapakila', 'slug' => 'tapakila']));

        $result = $this->workspaces->create('Tapakila');
        $this->assertSame('Tapakila', $result->name);
    }

    public function test_switch_to_posts_to_switch_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/workspaces/ws-2/switch', [], $this->anything())
            ->willReturn(array_merge($this->wsPayload, ['id' => 'ws-2']));

        $result = $this->workspaces->switchTo('ws-2');
        $this->assertSame('ws-2', $result->id);
    }
}
