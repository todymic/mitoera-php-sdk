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
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:         $data['id'],
            title:      $data['title'],
            identifier: $data['identifier'],
            chartId:    $data['chartId'] ?? null,
            chartName:  $data['chartName'] ?? null,
            createdAt:  isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
        );
    }
}
