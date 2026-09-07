<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\MitoeraClient;
use PHPUnit\Framework\TestCase;

class MitoeraClientTest extends TestCase
{
    public function test_sandbox_auto_detected_from_pk_test_prefix(): void
    {
        $client = new MitoeraClient(['keyId' => 'pk_test_abc123', 'secret' => 'sk_xxx']);

        $this->assertSame('/sandbox-api', $client->apiPrefix);
    }

    public function test_production_detected_from_pk_live_prefix(): void
    {
        $client = new MitoeraClient(['keyId' => 'pk_live_abc123', 'secret' => 'sk_xxx']);

        $this->assertSame('/api', $client->apiPrefix);
    }

    public function test_explicit_mode_overrides_key_prefix(): void
    {
        // pk_live_ key forced into sandbox via explicit option
        $client = new MitoeraClient([
            'keyId'  => 'pk_live_abc123',
            'secret' => 'sk_xxx',
            'mode'   => 'sandbox',
        ]);

        $this->assertSame('/sandbox-api', $client->apiPrefix);
    }

    public function test_throws_when_keyid_missing(): void
    {
        $this->expectException(AuthException::class);

        new MitoeraClient(['secret' => 'sk_xxx']);
    }

    public function test_throws_when_secret_missing(): void
    {
        $this->expectException(AuthException::class);

        new MitoeraClient(['keyId' => 'pk_live_abc']);
    }

    public function test_throws_when_both_missing(): void
    {
        $this->expectException(AuthException::class);

        new MitoeraClient([]);
    }
}
