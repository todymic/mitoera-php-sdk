<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Tests\Unit;

use Mitoera\Sdk\Exception\ApiException;
use Mitoera\Sdk\Exception\AuthException;
use Mitoera\Sdk\Exception\MitoeraException;
use PHPUnit\Framework\TestCase;

class ExceptionMappingTest extends TestCase
{
    public function test_401_maps_to_auth_exception(): void
    {
        $e = ApiException::fromResponse(401, null);

        $this->assertInstanceOf(AuthException::class, $e);
        $this->assertSame(401, $e->statusCode);
    }

    public function test_403_maps_to_auth_exception(): void
    {
        $this->assertInstanceOf(AuthException::class, ApiException::fromResponse(403, null));
    }

    /** An AuthException must stay catchable as ApiException — 401 threw one before. */
    public function test_auth_exception_is_still_an_api_exception(): void
    {
        $e = ApiException::fromResponse(401, null);

        $this->assertInstanceOf(ApiException::class, $e);
        $this->assertInstanceOf(MitoeraException::class, $e);
    }

    public function test_other_statuses_stay_plain_api_exceptions(): void
    {
        foreach ([400, 404, 409, 422, 500] as $status) {
            $e = ApiException::fromResponse($status, null);
            $this->assertInstanceOf(ApiException::class, $e);
            $this->assertNotInstanceOf(AuthException::class, $e, "HTTP $status");
        }
    }

    /** The regression this release exists for: a bare 401 said only "HTTP 401". */
    public function test_bare_401_message_names_its_causes(): void
    {
        $message = ApiException::fromResponse(
            401,
            null,
            'GET https://api.mitoera.com/api/charts',
            'pk_test_',
        )->getMessage();

        $this->assertStringNotContainsString('HTTP 401"', $message);
        $this->assertStringContainsString('revoked or has expired', $message);
        $this->assertStringContainsString('different key pairs', $message);
        $this->assertStringContainsString('GET https://api.mitoera.com/api/charts', $message);
        $this->assertStringContainsString('pk_test_…', $message);
        $this->assertStringContainsString('(sandbox)', $message);
        $this->assertStringContainsString('never a 401', $message);
    }

    public function test_production_key_is_labelled_production(): void
    {
        $message = ApiException::fromResponse(401, null, 'GET /x', 'pk_live_')->getMessage();

        $this->assertStringContainsString('(production)', $message);
    }

    /** The hint is a prefix, so no secret material can reach the message. */
    public function test_message_never_carries_the_secret(): void
    {
        $message = ApiException::fromResponse(401, ['message' => 'Invalid credentials'], 'GET /x', 'pk_test_')
            ->getMessage();

        $this->assertStringContainsString('Invalid credentials', $message);
        $this->assertStringNotContainsString('sk_', $message);
    }

    public function test_api_detail_is_preserved_when_present(): void
    {
        $e = ApiException::fromResponse(409, ['message' => 'Seat already held']);

        $this->assertSame('Seat already held', $e->getMessage());
        $this->assertSame(409, $e->statusCode);
    }

    public function test_body_is_exposed_on_auth_failures(): void
    {
        $e = ApiException::fromResponse(401, ['error' => 'token_expired']);

        $this->assertSame(['error' => 'token_expired'], $e->body);
    }

    public function test_bare_non_auth_error_names_the_request(): void
    {
        $e = ApiException::fromResponse(500, null, 'POST https://api.mitoera.com/api/charts');

        $this->assertSame('HTTP 500 on POST https://api.mitoera.com/api/charts', $e->getMessage());
    }

    /** Config errors predate any request: they carry no status code. */
    public function test_config_auth_exception_keeps_single_argument_form(): void
    {
        $e = new AuthException('Both "keyId" and "secret" are required.');

        $this->assertSame(0, $e->statusCode);
        $this->assertNull($e->body);
    }
}
