<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class ApiKeyResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $keyId,
        public string $scope,
        public bool $active,
        public ?\DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $lastUsedAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:         $data['id'],
            name:       $data['name'],
            keyId:      $data['keyId'],
            scope:      $data['scope'],
            active:     $data['active'] ?? true,
            createdAt:  isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
            lastUsedAt: isset($data['lastUsedAt']) ? new \DateTimeImmutable($data['lastUsedAt']) : null,
        );
    }
}
