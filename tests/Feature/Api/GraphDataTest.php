<?php

/**
 * GraphDataTest.php
 *
 * Tests for the JSON graph data API endpoint.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tristan Rhodes
 * @author     Tristan Rhodes <tristan.rhodes@gmail.com>
 */

namespace LibreNMS\Tests\Feature\Api;

use App\Models\ApiToken;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

class GraphDataTest extends DBTestCase
{
    use DatabaseTransactions;

    private User     $adminUser;
    private ApiToken $adminToken;
    private Device   $device;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminUser  = User::factory()->admin()->create();
        $this->adminToken = ApiToken::generateToken($this->adminUser);
        $this->device     = Device::factory()->create();
    }

    public function testGraphDataEndpointReturnsJson(): void
    {
        $this->json(
            'GET',
            "/api/v0/devices/{$this->device->hostname}/graphs/device_poller_perf/data",
            [],
            ['X-Auth-Token' => $this->adminToken->token_hash]
        )
        ->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'graph' => [
                'id', 'type', 'title', 'subtitle', 'unit',
                'from', 'to', 'step',
                'display' => ['renderer', 'kind', 'stacked', 'area', 'legend', 'tooltip'],
                'series',
                'meta' => ['source', 'fallback_used', 'empty_reason', 'warnings', 'generated_at'],
            ],
        ])
        ->assertJson(['status' => 'ok'])
        ->assertJson([
            'graph' => [
                'type' => 'device_poller_perf',
                'meta' => ['source' => 'rrd', 'fallback_used' => false],
            ],
        ]);
    }

    public function testGraphDataEndpointRequiresAuth(): void
    {
        $this->json('GET', "/api/v0/devices/{$this->device->hostname}/graphs/device_poller_perf/data")
             ->assertStatus(401);
    }

    public function testGraphDataEndpointReturnsErrorForUnsupportedType(): void
    {
        $this->json(
            'GET',
            "/api/v0/devices/{$this->device->hostname}/graphs/nonexistent_graph/data",
            [],
            ['X-Auth-Token' => $this->adminToken->token_hash]
        )
        ->assertStatus(404)
        ->assertJson(['status' => 'error']);
    }

    public function testGraphDataRespectsTimeRange(): void
    {
        $from = time() - 3600;
        $to   = time();

        $response = $this->json(
            'GET',
            "/api/v0/devices/{$this->device->hostname}/graphs/device_poller_perf/data?from={$from}&to={$to}",
            [],
            ['X-Auth-Token' => $this->adminToken->token_hash]
        )
        ->assertStatus(200)
        ->assertJson(['status' => 'ok']);

        $data = $response->json();
        $this->assertEquals($from, $data['graph']['from']);
        $this->assertEquals($to,   $data['graph']['to']);
    }

    public function testGraphDataSetsCacheControlForPastTimeRange(): void
    {
        $from = time() - 7200;
        $to   = time() - 3600;

        $response = $this->json(
            'GET',
            "/api/v0/devices/{$this->device->hostname}/graphs/device_poller_perf/data?from={$from}&to={$to}",
            [],
            ['X-Auth-Token' => $this->adminToken->token_hash]
        );

        $response->assertStatus(200);
        $this->assertTrue($response->headers->hasCacheControlDirective('private'));
        $this->assertEquals('300', $response->headers->getCacheControlDirective('max-age'));
    }

    public function testGraphDataSetsCacheControlNoStoreForLiveTimeRange(): void
    {
        $from = time() - 3600;
        $to   = time();

        $response = $this->json(
            'GET',
            "/api/v0/devices/{$this->device->hostname}/graphs/device_poller_perf/data?from={$from}&to={$to}",
            [],
            ['X-Auth-Token' => $this->adminToken->token_hash]
        );

        $response->assertStatus(200);
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
    }
}
