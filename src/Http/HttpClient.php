<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Mitoera\Sdk\Exception\ApiException;
use Mitoera\Sdk\Exception\MitoeraException;

/**
 * Thin Guzzle wrapper that:
 *  - always sends JSON
 *  - decodes JSON responses
 *  - converts HTTP errors to ApiException
 */
class HttpClient
{
    private Client $guzzle;

    public function __construct(
        private readonly string $baseUrl,
        private readonly int $timeout = 30,
    ) {
        $this->guzzle = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout'  => $this->timeout,
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent'   => 'mitoera-php-sdk/1.0',
            ],
        ]);
    }

    public function get(string $path, array $headers = []): array
    {
        return $this->request('GET', $path, [], $headers);
    }

    public function post(string $path, array $body = [], array $headers = []): array
    {
        return $this->request('POST', $path, $body, $headers);
    }

    public function put(string $path, array $body = [], array $headers = []): array
    {
        return $this->request('PUT', $path, $body, $headers);
    }

    public function patch(string $path, array $body = [], array $headers = []): array
    {
        return $this->request('PATCH', $path, $body, $headers);
    }

    public function delete(string $path, array $headers = []): array
    {
        return $this->request('DELETE', $path, [], $headers);
    }

    private function request(string $method, string $path, array $body, array $headers): array
    {
        $path = ltrim($path, '/');

        $options = ['headers' => $headers];
        if (!empty($body)) {
            $options['json'] = $body;
        }

        try {
            $response = $this->guzzle->request($method, $path, $options);
            $raw = (string) $response->getBody();

            return $raw === '' ? [] : (json_decode($raw, true) ?? []);
        } catch (ClientException | ServerException $e) {
            $status = $e->getResponse()->getStatusCode();
            $raw    = (string) $e->getResponse()->getBody();
            $body   = @json_decode($raw, true);

            throw ApiException::fromResponse($status, $body ?: null);
        } catch (\Throwable $e) {
            throw new MitoeraException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
