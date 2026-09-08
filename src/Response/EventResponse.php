<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class EventResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public string $identifier,
        public ?string $chartId,
        public ?string $chartName,
        public ?\DateTimeImmutable $createdAt,
        /** @var array<int, array<string, mixed>> raw {seatKey, status, ...} entries */
        public array $seats = [],
        /** @var array<int, array<string, mixed>> raw chart object tree (seatRows, freeZones, ...) */
        public array $chartObjects = [],
        /** @var array<int, array<string, mixed>> raw {id, name, color, ...} entries */
        public array $categories = [],
        public ?string $mercurePublicUrl = null,
        public ?string $chartSlug = null,
    ) {}

    /**
     * Some endpoints — /events/lookup/{identifier} in particular — return a
     * minimal {"id": "..."} payload rather than the full event resource, so
     * title/identifier must tolerate being absent instead of crashing with
     * a TypeError.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id:               $data['id'],
            title:            $data['title'] ?? '',
            identifier:       $data['identifier'] ?? '',
            chartId:          $data['chartId'] ?? null,
            chartName:        $data['chartName'] ?? null,
            createdAt:        isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            seats:            $data['seats'] ?? [],
            chartObjects:     $data['chartObjects'] ?? [],
            categories:       $data['categories'] ?? [],
            mercurePublicUrl: $data['mercurePublicUrl'] ?? null,
            chartSlug:        $data['chartSlug'] ?? null,
        );
    }
}
