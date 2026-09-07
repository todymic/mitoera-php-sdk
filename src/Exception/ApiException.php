<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Exception;

class ApiException extends MitoeraException
{
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?array $body = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function fromResponse(int $status, ?array $body): self
    {
        $message = $body['message'] ?? $body['detail'] ?? $body['error'] ?? "HTTP $status";

        return new self($message, $status, $body);
    }
}
