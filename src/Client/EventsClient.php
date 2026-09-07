<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\EventResponse;
use Mitoera\Sdk\Response\SeatStatusMap;

class EventsClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /**
     * List all events in the current workspace.
     *
     * @return EventResponse[]
     */
    public function listAll(): array
    {
        $data = $this->client->get("{$this->client->apiPrefix}/events");

        return array_map(EventResponse::fromArray(...), $data);
    }

    /**
     * Retrieve a single event by UUID.
     */
    public function get(string $eventId): EventResponse
    {
        $data = $this->client->get("{$this->client->apiPrefix}/events/{$eventId}");

        return EventResponse::fromArray($data);
    }

    /**
     * Find an event by its custom identifier (slug).
     */
    public function findByIdentifier(string $identifier): EventResponse
    {
        $data = $this->client->get("{$this->client->apiPrefix}/events/lookup/{$identifier}");

        return EventResponse::fromArray($data);
    }

    /**
     * Get seat statuses for an event.
     *
     * @param  string[]|null $seatKeys  Filter to specific keys; null = all seats
     */
    public function listSeats(string $eventId, ?array $seatKeys = null): SeatStatusMap
    {
        $path = "{$this->client->apiPrefix}/events/{$eventId}/seats";

        if (!empty($seatKeys)) {
            $path .= '?' . http_build_query(['seatKeys' => $seatKeys]);
        }

        return SeatStatusMap::fromArray($this->client->get($path));
    }

    /**
     * Bulk-update seat statuses (backoffice override).
     *
     * @param  string[] $seatKeys
     * @param  string   $status  available|hold|booked|canceled
     */
    public function bulkUpdateSeats(string $eventId, array $seatKeys, string $status): int
    {
        $data = $this->client->patch(
            "{$this->client->apiPrefix}/events/{$eventId}/seats/bulk-status",
            ['seatKeys' => $seatKeys, 'status' => $status],
        );

        return $data['updated'] ?? count($seatKeys);
    }

    /**
     * Create a new event.
     */
    public function create(string $title, string $identifier, ?string $chartId = null): EventResponse
    {
        $body = array_filter(['title' => $title, 'identifier' => $identifier, 'chartId' => $chartId]);

        return EventResponse::fromArray(
            $this->client->post("{$this->client->apiPrefix}/events", $body)
        );
    }

    /**
     * Update an event's title or identifier.
     */
    public function update(string $eventId, array $fields): EventResponse
    {
        return EventResponse::fromArray(
            $this->client->put("{$this->client->apiPrefix}/events/{$eventId}", $fields)
        );
    }

    /**
     * Link a seating chart to an event.
     */
    public function linkChart(string $eventId, string $chartId): void
    {
        $this->client->post("{$this->client->apiPrefix}/events/{$eventId}/link-chart/{$chartId}");
    }

    /**
     * Delete an event.
     */
    public function delete(string $eventId): void
    {
        $this->client->delete("{$this->client->apiPrefix}/events/{$eventId}");
    }
}
