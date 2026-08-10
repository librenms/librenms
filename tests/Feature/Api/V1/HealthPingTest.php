<?php

/**
 * HealthPingTest.php
 *
 * Tests for the v1 liveness (ping) and health-check endpoints.
 */

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;

final class HealthPingTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // The v1 gate is enforced at request time by the EnsureApiV1Enabled
        // middleware, so a runtime-only config set is enough; it does not
        // persist beyond this test's application instance.
        LibrenmsConfig::set('api.v1.enabled', true);
    }

    public function testV1IsDisabledByDefault(): void
    {
        LibrenmsConfig::set('api.v1.enabled', false);

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
