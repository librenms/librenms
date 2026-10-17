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
