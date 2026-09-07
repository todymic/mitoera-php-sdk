<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Client\EventsClient;
use Mitoera\Sdk\Client\HoldsClient;
use Mitoera\Sdk\Client\SessionsClient;
use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\MitoeraClient;
use PHPUnit\Framework\TestCase;

class MitoeraClientTest extends TestCase
{
    public function test_boots_with_public_key(): void
    {
        $client = new MitoeraClient(['publicKey' => 'sk_pub_test_abc']);

        $this->assertInstanceOf(HoldsClient::class, $client->holds);
        $this->assertInstanceOf(SessionsClient::class, $client->sessions);
        $this->assertInstanceOf(EventsClient::class, $client->events);
    }

    public function test_boots_with_backoffice_key(): void
    {
        // TokenManager only calls embed-token lazily, so construction succeeds without network.
        $client = new MitoeraClient([
            'keyId'  => 'sk_bo_test',
            'secret' => 'supersecret',
        ]);

        $this->assertInstanceOf(HoldsClient::class, $client->holds);
    }

    public function test_throws_on_missing_credentials(): void
    {
        $this->expectException(AuthException::class);

        new MitoeraClient(['baseUrl' => 'https://api.mitoera.com']);
    }
}
