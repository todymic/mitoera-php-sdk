<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Http;

use Mitoera\Sdk\Exception\ApiException;
use Mitoera\Sdk\Exception\MitoeraException;

/**
 * Minimal cURL-based HTTP client — zero external dependencies.
 *
 * The only runtime requirement is the php-curl extension (bundled with
 * virtually every PHP distribution). Guzzle, Symfony HttpClient, and any
 * other third-party client are intentionally excluded so that consumers of
 * the SDK are free to use whichever HTTP stack they already have without
 * adding a dependency conflict.
 */
class HttpClient
{
    private readonly string $baseUrl;
    private readonly int $timeout;
    private readonly ?string $keyHint;

    /**
     * @param string|null $keyHint Key prefix ("pk_test_") quoted back in
     *                             authentication errors. Never the full keyId,
     *                             never the secret.
     */
    public function __construct(string $baseUrl, int $timeout = 30, ?string $keyHint = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->keyHint = $keyHint;
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
        $url = $this->baseUrl . '/' . ltrim($path, '/');

        $curlHeaders = [
            'Accept: application/json',
            'Content-Type: application/json',
            'User-Agent: mitoera-php-sdk/1.0',
        ];

        foreach ($headers as $name => $value) {
            $curlHeaders[] = "{$name}: {$value}";
        }

        $ch = curl_init();

        curl_setopt_array($ch, [
            \CURLOPT_URL            => $url,
            \CURLOPT_CUSTOMREQUEST  => $method,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_TIMEOUT        => $this->timeout,
            \CURLOPT_HTTPHEADER     => $curlHeaders,
        ]);

        if ($body !== []) {
            curl_setopt($ch, \CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw    = curl_exec($ch);
        $status = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $error  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            throw new MitoeraException('HTTP request failed: ' . $error);
        }

        $decoded = $raw !== '' ? @json_decode((string) $raw, true) : [];

        if ($status >= 400) {
            throw ApiException::fromResponse(
                $status,
                is_array($decoded) ? $decoded : null,
                "$method $url",
                $this->keyHint,
            );
        }

        return is_array($decoded) ? $decoded : [];
    }
}
