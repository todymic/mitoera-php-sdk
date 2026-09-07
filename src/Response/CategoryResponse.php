<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Response;

readonly class CategoryResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public int $key,
        public string $color,
        public ?string $chartId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:      $data['id'],
            name:    $data['name'],
            key:     (int) $data['key'],
            color:   $data['color'] ?? '#000000',
            chartId: $data['chartId'] ?? null,
        );
    }
}
