<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\Http\HttpClient;
use Mitoera\Sdk\Response\BookResponse;
use Mitoera\Sdk\Response\HoldResponse;

class HoldsClient
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly \Closure $authHeaders,
        private readonly string $apiPrefix,
    ) {}

    /**
     * Block seats for a user session.
     *
     * @param  string[] $seatKeys
     */
    public function hold(string $eventId, array $seatKeys, string $holdToken): HoldResponse
    {
        $data = $this->http->post(
            "{$this->apiPrefix}/events/{$eventId}/hold",
            ['seatKeys' => $seatKeys, 'holdToken' => $holdToken],
            ($this->authHeaders)(),
        );

        return HoldResponse::fromArray($data);
    }

    /**
     * Confirm the reservation after successful payment.
     *
     * @param  string[] $seatKeys
     */
    public function book(string $eventId, array $seatKeys, string $holdToken): BookResponse
    {
        $data = $this->http->post(
            "{$this->apiPrefix}/events/{$eventId}/book",
            ['seatKeys' => $seatKeys, 'holdToken' => $holdToken],
            ($this->authHeaders)(),
        );

        return BookResponse::fromArray($data);
    }

    /**
     * Release previously held seats (e.g. payment timeout or cancellation).
     *
     * @param  string[] $seatKeys
     */
    public function release(string $eventId, array $seatKeys, string $holdToken): void
    {
        $this->http->post(
            "{$this->apiPrefix}/events/{$eventId}/release",
            ['seatKeys' => $seatKeys, 'holdToken' => $holdToken],
            ($this->authHeaders)(),
        );
    }

    /**
     * Force-set seat status (backoffice override — requires ROLE_BACKOFFICE).
     *
     * @param  string[] $seatKeys
     * @param  string   $status  available|hold|booked|canceled
     */
    public function changeStatus(string $eventId, array $seatKeys, string $status): void
    {
        $this->http->post(
            "{$this->apiPrefix}/events/{$eventId}/change-status",
            ['seatKeys' => $seatKeys, 'status' => $status],
            ($this->authHeaders)(),
        );
    }
}
