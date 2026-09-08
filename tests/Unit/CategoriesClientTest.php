<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\CategoriesClient;
use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\CategoryResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoriesClientTest extends TestCase
{
    private HttpClient&MockObject $http;
    private CategoriesClient $categories;

    private array $catPayload = [
        'id'      => 'cat-1',
        'name'    => 'VIP',
        'key'     => 1,
        'color'   => '#FFD700',
        'chartId' => 'chart-1',
    ];

    protected function setUp(): void
    {
        $this->http       = $this->createMock(HttpClient::class);
        $core             = new MitoeraClient(['keyId' => 'pk_live_test', 'secret' => 'sk_xxx'], $this->http);
        $this->categories = new CategoriesClient($core);
    }

    public function test_list_for_chart_returns_array_of_responses(): void
    {
        $this->http->expects($this->once())
            ->method('get')
            ->with('/api/charts/chart-1/categories', $this->anything())
            ->willReturn([$this->catPayload]);

        $result = $this->categories->listForChart('chart-1');

        $this->assertCount(1, $result);
        $this->assertInstanceOf(CategoryResponse::class, $result[0]);
        $this->assertSame('VIP', $result[0]->name);
    }

    public function test_create_posts_name_and_color(): void
    {
        $this->http->expects($this->once())
            ->method('post')
            ->with('/api/charts/chart-1/categories', ['name' => 'VIP', 'color' => '#FFD700'], $this->anything())
            ->willReturn($this->catPayload);

        $result = $this->categories->create('chart-1', 'VIP', '#FFD700');
        $this->assertSame(1, $result->key);
    }

    public function test_delete_calls_correct_endpoint(): void
    {
        $this->http->expects($this->once())
            ->method('delete')
            ->with('/api/charts/chart-1/categories/1', $this->anything())
            ->willReturn([]);

        $this->categories->delete('chart-1', '1');
    }
}
