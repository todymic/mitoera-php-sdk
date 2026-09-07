<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\BookResponse;
use Mitoera\Sdk\Response\HoldResponse;

class HoldsClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /**
     * Block seats for a user session.
     *
     * @param string[] $seatKeys
     */
    public function hold(string $eventId, array $seatKeys, string $holdToken): HoldResponse
    {
        $data = $this->client->post(
            "{$this->client->apiPrefix}/events/{$eventId}/hold",
            ['seatKeys' => $seatKeys, 'holdToken' => $holdToken],
        );

        return HoldResponse::fromArray($data);
    }

    /**
     * Confirm the reservation after successful payment.
     *
     * @param string[] $seatKeys
     */
    public function book(string $eventId, array $seatKeys, string $holdToken): BookResponse
    {
        $data = $this->client->post(
            "{$this->client->apiPrefix}/events/{$eventId}/book",
            ['seatKeys' => $seatKeys, 'holdToken' => $holdToken],
        );

        return BookResponse::fromArray($data);
    }

    /**
     * Release held seats (payment timeout / cancellation).
     *
     * @param string[] $seatKeys
     */
    public function release(string $eventId, array $seatKeys, string $holdToken): void
    {
        $this->client->post(
            "{$this->client->apiPrefix}/events/{$eventId}/release",
            ['seatKeys' => $seatKeys, 'holdToken' => $holdToken],
        );
    }

    /**
     * Force-set seat status (backoffice override).
     *
     * @param string[] $seatKeys
     * @param string   $status  available|hold|booked|canceled
     */
    public function changeStatus(string $eventId, array $seatKeys, string $status): void
    {
        $this->client->post(
            "{$this->client->apiPrefix}/events/{$eventId}/change-status",
            ['seatKeys' => $seatKeys, 'status' => $status],
        );
    }
}
