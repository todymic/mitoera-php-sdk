<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\CategoryResponse;

/** @internal Accessed via $client->categories */
class CategoriesClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /** @return CategoryResponse[] */
    public function listForChart(string $chartId): array
    {
        $data = $this->client->get(
            $this->client->apiPrefix . '/charts/' . $chartId . '/categories'
        );
        return array_map(CategoryResponse::fromArray(...), $data['items'] ?? $data);
    }

    public function get(string $chartId, int $categoryKey): CategoryResponse
    {
        return CategoryResponse::fromArray(
            $this->client->get(
                $this->client->apiPrefix . '/charts/' . $chartId . '/categories/' . $categoryKey
            )
        );
    }

    public function create(string $chartId, string $name, string $color, ?string $key = null, int|float $price = 0): CategoryResponse
    {
        $body = ['name' => $name, 'color' => $color];
        if ($key !== null && $key !== '') {
            $body['key'] = $key;
        }
        if ($price !== 0) {
            $body['price'] = $price;
        }

        return CategoryResponse::fromArray(
            $this->client->post(
                $this->client->apiPrefix . '/charts/' . $chartId . '/categories',
                $body,
            )
        );
    }

    public function update(string $chartId, string $categoryKey, array $fields): CategoryResponse
    {
        return CategoryResponse::fromArray(
            $this->client->put(
                $this->client->apiPrefix . '/charts/' . $chartId . '/categories/' . rawurlencode($categoryKey),
                $fields,
            )
        );
    }

    public function delete(string $chartId, string $categoryKey): void
    {
        $this->client->delete(
            $this->client->apiPrefix . '/charts/' . $chartId . '/categories/' . rawurlencode($categoryKey)
        );
    }
}
