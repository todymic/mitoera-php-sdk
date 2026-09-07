<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

/**
 * Returned once on key creation — the secret field is never returned again.
 */
readonly class ApiKeyCreatedResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $keyId,
        public string $secret,
        public string $scope,
        public ?\DateTimeImmutable $createdAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:        $data['id'],
            name:      $data['name'],
            keyId:     $data['keyId'],
            secret:    $data['secret'],
            scope:     $data['scope'],
            createdAt: isset($data['createdAt']) ? new \DateTimeImmutable($data['createdAt']) : null,
        );
    }
}
