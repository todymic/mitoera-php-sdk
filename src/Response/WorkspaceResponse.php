<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class WorkspaceResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public bool $current,
        public ?\DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:        $data['id'],
            name:      $data['name'],
            slug:      $data['slug'],
            current:   $data['current'] ?? false,
            createdAt: isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
        );
    }
}
