<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class SessionResponse
{
    public function __construct(
        public string $sessionToken,
        public string $holdToken,
        public string $eventId,
        public int $expiresIn,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionToken: $data['sessionToken'],
            holdToken:    $data['holdToken'],
            eventId:      $data['eventId'],
            expiresIn:    $data['expiresIn'] ?? 600,
        );
    }
}
