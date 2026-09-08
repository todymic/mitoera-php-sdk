<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;

/** @internal Accessed via $client->reporting */
class ReportingClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /**
     * Monthly seat usage grouped by month.
     *
     * GET /api/reporting/seats/monthly
     *
     * @return array<int, array{month: string, totalSeats: int, ...}>
     */
    public function monthly(?string $userId = null, ?int $year = null): array
    {
        return $this->client->get($this->buildUrl('/seats/monthly', compact('userId', 'year')));
    }

    /**
     * Seat usage grouped by event for a given month.
     *
     * GET /api/reporting/seats/by-event
     *
     * @return array<int, array{eventId: string, totalSeats: int, ...}>
     */
    public function byEvent(?string $userId = null, ?int $year = null, ?int $month = null): array
    {
        return $this->client->get($this->buildUrl('/seats/by-event', compact('userId', 'year', 'month')));
    }

    /**
     * Detailed seat list for a single event.
     *
     * GET /api/reporting/seats/event/{eventId}
     *
     * @return array<string, mixed>
     */
    public function seatList(string $eventId): array
    {
        return $this->client->get($this->client->apiPrefix . '/reporting/seats/event/' . rawurlencode($eventId));
    }

    /**
     * All workspaces with their seat counts for a given month.
     *
     * GET /api/reporting/seats/workspaces
     *
     * @return array<int, array{workspaceId: string, totalSeats: int, ...}>
     */
    public function workspaces(?int $year = null, ?int $month = null): array
    {
        return $this->client->get($this->buildUrl('/seats/workspaces', compact('year', 'month')));
    }

    private function buildUrl(string $path, array $params): string
    {
        $query = http_build_query(array_filter($params, fn($v) => $v !== null));
        $url   = $this->client->apiPrefix . '/reporting' . $path;

        return $query !== '' ? $url . '?' . $query : $url;
    }
}
