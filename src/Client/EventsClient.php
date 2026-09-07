<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\Response\EventResponse;
use Mitoera\Sdk\Response\SeatStatusMap;

class EventsClient
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly \Closure $authHeaders,
        private readonly string $apiPrefix,
    ) {}

    /**
     * List all events in the current workspace.
     *
     * @return EventResponse[]
     */
    public function listAll(): array
    {
        $data = $this->http->get("{$this->apiPrefix}/events", ($this->authHeaders)());

        return array_map(EventResponse::fromArray(...), $data);
    }

    /**
     * Retrieve a single event by UUID or slug.
     */
    public function get(string $eventId): EventResponse
    {
        $data = $this->http->get("{$this->apiPrefix}/events/{$eventId}", ($this->authHeaders)());

        return EventResponse::fromArray($data);
    }

    /**
     * Find an event by its custom identifier (slug).
     */
    public function findByIdentifier(string $identifier): EventResponse
    {
        $data = $this->http->get(
            "{$this->apiPrefix}/events/lookup/{$identifier}",
            ($this->authHeaders)(),
        );

        return EventResponse::fromArray($data);
    }

    /**
     * Get seat statuses for an event.
     *
     * @param  string[]|null $seatKeys  Filter to specific keys, null = all seats
     */
    public function listSeats(string $eventId, ?array $seatKeys = null): SeatStatusMap
    {
        $path = "{$this->apiPrefix}/events/{$eventId}/seats";

        if (!empty($seatKeys)) {
            $query = http_build_query(['seatKeys' => $seatKeys]);
            $path  .= '?' . $query;
        }

        $data = $this->http->get($path, ($this->authHeaders)());

        return SeatStatusMap::fromArray($data);
    }

    /**
     * Bulk-update seat statuses (backoffice override).
     *
     * @param  string[] $seatKeys
     * @param  string   $status  available|hold|booked|canceled
     */
    public function bulkUpdateSeats(string $eventId, array $seatKeys, string $status): int
    {
        $data = $this->http->patch(
            "{$this->apiPrefix}/events/{$eventId}/seats/bulk-status",
            ['seatKeys' => $seatKeys, 'status' => $status],
            ($this->authHeaders)(),
        );

        return $data['updated'] ?? count($seatKeys);
    }

    /**
     * Create a new event.
     */
    public function create(string $title, string $identifier, ?string $chartId = null): EventResponse
    {
        $body = array_filter([
            'title'      => $title,
            'identifier' => $identifier,
            'chartId'    => $chartId,
        ]);

        $data = $this->http->post("{$this->apiPrefix}/events", $body, ($this->authHeaders)());

        return EventResponse::fromArray($data);
    }

    /**
     * Update an event's title or identifier.
     */
    public function update(string $eventId, array $fields): EventResponse
    {
        $data = $this->http->put(
            "{$this->apiPrefix}/events/{$eventId}",
            $fields,
            ($this->authHeaders)(),
        );

        return EventResponse::fromArray($data);
    }

    /**
     * Link a seating chart to an event.
     */
    public function linkChart(string $eventId, string $chartId): void
    {
        $this->http->post(
            "{$this->apiPrefix}/events/{$eventId}/link-chart/{$chartId}",
            [],
            ($this->authHeaders)(),
        );
    }

    /**
     * Delete an event.
     */
    public function delete(string $eventId): void
    {
        $this->http->delete("{$this->apiPrefix}/events/{$eventId}", ($this->authHeaders)());
    }
}
