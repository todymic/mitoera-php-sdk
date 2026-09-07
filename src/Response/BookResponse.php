<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class BookResponse
{
    public function __construct(
        /** @var string[] */
        public array $bookedSeats,
        public string $eventId,
        public \DateTimeImmutable $bookedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            bookedSeats: $data['bookedSeats'] ?? [],
            eventId:     $data['eventId'],
            bookedAt:    new \DateTimeImmutable($data['bookedAt']),
        );
    }
}
