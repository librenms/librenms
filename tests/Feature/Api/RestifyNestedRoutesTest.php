<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Mempool;
use App\Models\OspfNbr;
use App\Models\Port;
use App\Models\Sensor;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RestifyNestedRoutesTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);

        $apiAccess = Permission::findOrCreate('api.access');
        Role::findOrCreate('admin')->givePermissionTo($apiAccess);
    }

    public function testDevicePortsNestedRoute(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Port::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}/ports");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function testDevicePortsNestedRouteReturnsOnlyDevicePorts(): void
    {
        $user = User::factory()->admin()->create();
        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();
        Port::factory()->count(2)->for($device1)->create();
        Port::factory()->count(3)->for($device2)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device1->device_id}/ports");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testDeviceSensorsNestedRoute(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sensor::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}/sensors");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testDeviceMemoryPoolsNestedRoute(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Mempool::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}/memory-pools");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testDeviceBgpPeersNestedRoute(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        BgpPeer::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}/bgp-peers");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testDeviceOspfNeighborsNestedRoute(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfNbr::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}/ospf-neighbors");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function testNestedRouteRequiresAuth(): void
    {
        $device = Device::factory()->create();

        $this->getJson("/api/v1/devices/{$device->device_id}/ports")
            ->assertStatus(401);
    }

    public function testNestedRouteWithInvalidRelationshipReturnsError(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/devices/{$device->device_id}/nonexistent")
            ->assertStatus(500);
    }
}
