<?php

/**
 * HealthPingTest.php
 *
 * Tests for the v1 liveness (ping) and health-check endpoints.
 */

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;

final class HealthPingTest extends DBTestCase
{
    /*
     * No DatabaseTransactions here: the api.v1.enabled setting decides route
     * registration at application boot, so it must be committed to the DB
     * before the application is (re)built — a transaction-wrapped write would
     * be invisible to the fresh application, and refreshApplication() leaves
     * the transaction behind anyway. All writes are cleaned up explicitly.
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->setV1Enabled(true);
    }

    protected function tearDown(): void
    {
        LibrenmsConfig::erase('api.v1.enabled');

        parent::tearDown();
    }

    /**
     * Persist the setting, then boot a fresh application so route
     * registration (which runs at boot) sees the new value.
     */
    private function setV1Enabled(bool $enabled): void
    {
        if ($enabled) {
            LibrenmsConfig::persist('api.v1.enabled', true);
        } else {
            LibrenmsConfig::erase('api.v1.enabled');
        }

        $this->refreshApplication();
    }

    public function testV1IsDisabledByDefault(): void
    {
        $this->setV1Enabled(false);

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

        $user->delete();
    }
}
