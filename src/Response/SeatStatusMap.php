<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

/**
 * Map of seatKey → status returned by EventsClient::listSeats().
 *
 * @implements \ArrayAccess<string, string>
 * @implements \IteratorAggregate<string, string>
 */
class SeatStatusMap implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /** @param array<string, string> $seats */
    public function __construct(private readonly array $seats) {}

    public static function fromArray(array $data): self
    {
        return new self($data['seats'] ?? $data);
    }

    public function status(string $seatKey): ?string
    {
        return $this->seats[$seatKey] ?? null;
    }

    public function isAvailable(string $seatKey): bool
    {
        return $this->status($seatKey) === 'available';
    }

    public function byStatus(string $status): array
    {
        return array_keys(array_filter($this->seats, fn($s) => $s === $status));
    }

    public function available(): array { return $this->byStatus('available'); }
    public function held(): array      { return $this->byStatus('hold'); }
    public function booked(): array    { return $this->byStatus('booked'); }

    public function toArray(): array { return $this->seats; }

    // ArrayAccess
    public function offsetExists(mixed $offset): bool  { return isset($this->seats[$offset]); }
    public function offsetGet(mixed $offset): ?string  { return $this->seats[$offset] ?? null; }
    public function offsetSet(mixed $offset, mixed $value): void { /* immutable */ }
    public function offsetUnset(mixed $offset): void            { /* immutable */ }

    public function getIterator(): \ArrayIterator { return new \ArrayIterator($this->seats); }
    public function count(): int                  { return count($this->seats); }
}
