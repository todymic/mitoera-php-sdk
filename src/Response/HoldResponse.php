<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class HoldResponse
{
    public function __construct(
        public string $holdToken,
        /** @var string[] */
        public array $seatKeys,
        public \DateTimeImmutable $expiresAt,
        public int $durationSeconds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            holdToken:       $data['holdToken'] ?? '',
            seatKeys:        $data['seatKeys'] ?? [],
            expiresAt:       isset($data['expiresAt']) ? new \DateTimeImmutable($data['expiresAt']) : new \DateTimeImmutable('+10 minutes'),
            durationSeconds: $data['durationSeconds'] ?? 600,
        );
    }
}
