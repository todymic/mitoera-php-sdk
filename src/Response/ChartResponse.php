<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class ChartResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public array $objects,
        public \DateTimeImmutable $updatedAt,
        public string $status,
        public bool $pendingChanges,
        public ?array $publishedSnapshot,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:                $data['id'],
            name:              $data['name'],
            objects:           $data['objects'] ?? [],
            updatedAt:         new \DateTimeImmutable($data['updatedAt']),
            status:            $data['status'] ?? 'draft',
            pendingChanges:    $data['pendingChanges'] ?? false,
            publishedSnapshot: $data['publishedSnapshot'] ?? null,
        );
    }

    public function isPublished(): bool { return $this->status === 'published'; }
    public function isDraft(): bool     { return $this->status === 'draft'; }
}
