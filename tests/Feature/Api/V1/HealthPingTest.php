<?php

/**
 * HealthPingTest.php
 *
 * Tests for the v1 liveness (ping) and health-check endpoints.
 */

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;

final class HealthPingTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        // v1 route registration reads api.v1.enabled (API_V1_ENABLED env)
        // during application boot, so the env value must be in place before
        // parent::setUp() creates the application.
        self::putEnv('API_V1_ENABLED', 'true');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        self::putEnv('API_V1_ENABLED', null);
    }

    private static function putEnv(string $name, ?string $value): void
    {
        if ($value === null) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);

            return;
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    public function testV1IsDisabledByDefault(): void
    {
        // An explicit 'false' (not an unset) so a stray API_V1_ENABLED in a
        // local .env cannot leak in when the application re-reads it.
        self::putEnv('API_V1_ENABLED', 'false');
        $this->refreshApplication();

        $this->getJson('/api/v1/ping')->assertNotFound();
        $this->getJson('/api/v1/health')->assertNotFound();
    }

    public function testPingIsPublicAndReturnsOk(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertOk()
            ->assertExactJson(['status' => 'ok']);
    }

    public function testHealthRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/health')
            ->assertUnauthorized();
    }

    public function testHealthReturnsOkWhenSubsystemsAreUp(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJson([
                'status' => 'ok',
                'checks' => [
                    'database' => ['ok' => true],
                    'cache' => ['ok' => true],
                ],
            ])
            ->assertJsonStructure([
                'status',
                'checks' => [
                    'database' => ['ok'],
                    'cache' => ['ok'],
                ],
            ]);
    }
}
