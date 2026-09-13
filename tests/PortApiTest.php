<?php

namespace LibreNMS\Tests;

use App\Models\ApiToken;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

final class PortApiTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testUpdatePortSpeed(): void
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create([
            'ifName' => 'ether2',
            'ifSpeed' => 10000000,
        ]);

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => 1000000000,
        ], ['X-Auth-Token' => $token->token_hash])
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
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create([
            'ifName' => 'ether2',
            'ifSpeed' => 1000000000,
        ]);
        $device->setAttrib('ifSpeed:ether2', 1000000000);

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => 0,
        ], ['X-Auth-Token' => $token->token_hash])
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
        /** @var User $user */
        $user = User::factory()->admin()->create();
        $token = ApiToken::generateToken($user);
        $port = Port::factory()->for(Device::factory())->create();

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => -1,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function testReadOnlyUserCannotUpdatePortSpeed(): void
    {
        /** @var User $user */
        $user = User::factory()->read()->create();
        $token = ApiToken::generateToken($user);
        $port = Port::factory()->for(Device::factory())->create();

        $this->json('PATCH', "/api/v0/ports/{$port->port_id}/speed", [
            'speed' => 1000000000,
        ], ['X-Auth-Token' => $token->token_hash])
            ->assertStatus(403);
    }
}
