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
