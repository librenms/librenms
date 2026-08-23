<?php

namespace LibreNMS\Tests;

use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;

final class PortApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testUserWithUpdatePermissionCanUpdatePortDescription(): void
    {
        $token = $this->portUpdateUser()->createToken('test');
        $port = Port::factory()->for(Device::factory())->create(['ifAlias' => 'old']);

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/description", ['description' => 'uplink'], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertSame('uplink', $port->fresh()->ifAlias);
    }

    public function testUserWithUpdatePermissionCanUpdateDevicePortNotes(): void
    {
        $token = $this->portUpdateUser()->createToken('test');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();

        $this->json('PATCH', "/api/v0/devices/{$device->device_id}/port/{$port->port_id}", ['notes' => 'core link'], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJsonPath('status', 'ok');

        $this->assertSame('core link', $device->getAttrib('port_id_notes:' . $port->port_id));
    }

    public function testUserWithoutUpdatePermissionCannotUpdatePortDescription(): void
    {
        /** @var User $user */
        $user = User::factory()->read()->create();
        $token = $user->createToken('test');
        $port = Port::factory()->for(Device::factory())->create(['ifAlias' => 'old']);

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/description", ['description' => 'uplink'], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(403);

        $this->assertSame('old', $port->fresh()->ifAlias);
    }

    public function testUpdatePortSpeed(): void
    {
        $token = $this->portUpdateUser()->createToken('test');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create([
            'ifName' => 'ether2',
            'ifSpeed' => 10000000,
        ]);

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => 1000000000,
        ], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'message' => 'Port speed updated.',
            ]);

        $this->assertDatabaseHas('ports', [
            'port_id' => $port->port_id,
            'ifSpeed' => 1000000000,
        ]);
        $this->assertDatabaseHas('devices_attribs', [
            'device_id' => $device->device_id,
            'attrib_type' => 'ifSpeed:ether2',
            'attrib_value' => '1000000000',
        ]);
        $this->assertDatabaseHas('eventlog', [
            'device_id' => $device->device_id,
            'type' => 'interface',
            'reference' => $port->port_id,
            'message' => 'ether2 Port speed set via API: 1000000000',
        ]);
    }

    public function testClearPortSpeedOverride(): void
    {
        $token = $this->portUpdateUser()->createToken('test');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create([
            'ifName' => 'ether2',
            'ifSpeed' => 1000000000,
        ]);
        $device->setAttrib('ifSpeed:ether2', 1000000000);

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => 0,
        ], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'message' => 'Port speed override cleared.',
            ]);

        $this->assertDatabaseHas('ports', [
            'port_id' => $port->port_id,
            'ifSpeed' => 0,
        ]);
        $this->assertDatabaseMissing('devices_attribs', [
            'device_id' => $device->device_id,
            'attrib_type' => 'ifSpeed:ether2',
        ]);
    }

    public function testUpdatePortSpeedValidatesSpeed(): void
    {
        $token = $this->portUpdateUser()->createToken('test');
        $port = Port::factory()->for(Device::factory())->create();

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => -1,
        ], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function testReadOnlyUserCannotUpdatePortSpeed(): void
    {
        /** @var User $user */
        $user = User::factory()->read()->create();
        $token = $user->createToken('test');
        $port = Port::factory()->for(Device::factory())->create();

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => 1000000000,
        ], ['X-Auth-Token' => $token->plainTextToken])
            ->assertStatus(403);
    }

    /**
     * A non-admin user who may read every device and update ports.
     * Admins skip policies entirely, so they never exercise this check.
     */
    private function portUpdateUser(): User
    {
        /** @var User $user */
        $user = User::factory()->read()->create();
        $user->givePermissionTo(Permission::findOrCreate('port.update', 'web'));

        return $user;
    }
}
