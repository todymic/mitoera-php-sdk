<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\ChartResponse;

/** @internal Accessed via $client->charts */
class ChartsClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    public function listAll(): array
    {
        $data = $this->client->get($this->client->apiPrefix . '/charts');
        return array_map(ChartResponse::fromArray(...), $data['items'] ?? $data);
    }

    public function get(string $chartId): ChartResponse
    {
        return ChartResponse::fromArray(
            $this->client->get($this->client->apiPrefix . '/charts/' . $chartId)
        );
    }

    public function create(string $name): ChartResponse
    {
        return ChartResponse::fromArray(
            $this->client->post($this->client->apiPrefix . '/charts', ['name' => $name])
        );
    }

    public function update(string $chartId, array $fields): ChartResponse
    {
        return ChartResponse::fromArray(
            $this->client->put($this->client->apiPrefix . '/charts/' . $chartId, $fields)
        );
    }

    /**
     * Replace the full object tree of a chart.
     *
     * @param array $objects The complete set of seat/shape objects for this chart.
     */
    public function setObjects(string $chartId, array $objects): ChartResponse
    {
        return ChartResponse::fromArray(
            $this->client->put(
                $this->client->apiPrefix . '/charts/' . $chartId . '/objects',
                ['objects' => $objects],
            )
        );
    }

    public function publish(string $chartId): ChartResponse
    {
        return ChartResponse::fromArray(
            $this->client->post($this->client->apiPrefix . '/charts/' . $chartId . '/publish')
        );
    }

    public function markPending(string $chartId): ChartResponse
    {
        return ChartResponse::fromArray(
            $this->client->post($this->client->apiPrefix . '/charts/' . $chartId . '/mark-pending')
        );
    }

    public function delete(string $chartId): void
    {
        $this->client->delete($this->client->apiPrefix . '/charts/' . $chartId);
    }
}
