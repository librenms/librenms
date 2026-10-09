<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * %2F is decoded before route matching, so names containing '/' must still match one route parameter.
 */
final class SlashInNameApiTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, array{string, string, string, array<string, string>}>
     */
    public static function namedRoutes(): array
    {
        return [
            'get location' => ['GET', '/api/v0/location/DC1%2FRoom%202', 'get_location', ['location_id_or_name' => 'DC1/Room 2']],
            'edit location' => ['PATCH', '/api/v0/locations/DC1%2FRoom%202', 'edit_location', ['location_id_or_name' => 'DC1/Room 2']],
            'delete location' => ['DELETE', '/api/v0/locations/DC1%2FRoom%202', 'del_location', ['location' => 'DC1/Room 2']],
            'location maintenance' => ['POST', '/api/v0/locations/DC1%2FRoom%202/maintenance', 'maintenance_location', ['location' => 'DC1/Room 2']],
            'get device group' => ['GET', '/api/v0/devicegroups/Site%20A%2FCore', 'get_devices_by_group', ['name' => 'Site A/Core']],
            'update device group' => ['PATCH', '/api/v0/devicegroups/Site%20A%2FCore', 'update_device_group', ['name' => 'Site A/Core']],
            'delete device group' => ['DELETE', '/api/v0/devicegroups/Site%20A%2FCore', 'delete_device_group', ['name' => 'Site A/Core']],
            'add group devices' => ['POST', '/api/v0/devicegroups/Site%20A%2FCore/devices', 'update_device_group_add_devices', ['name' => 'Site A/Core']],
            'remove group devices' => ['DELETE', '/api/v0/devicegroups/Site%20A%2FCore/devices', 'update_device_group_remove_devices', ['name' => 'Site A/Core']],
            'group maintenance' => ['POST', '/api/v0/devicegroups/Site%20A%2FCore/maintenance', 'maintenance_devicegroup', ['name' => 'Site A/Core']],
            'plain remove group devices' => ['DELETE', '/api/v0/devicegroups/core/devices', 'update_device_group_remove_devices', ['name' => 'core']],
            'plain delete device group' => ['DELETE', '/api/v0/devicegroups/core', 'delete_device_group', ['name' => 'core']],
            // same decoded path, so a group named "Core/devices" can't be addressed by name
            'name ending in a sub-route' => ['DELETE', '/api/v0/devicegroups/Core%2Fdevices', 'update_device_group_remove_devices', ['name' => 'Core']],
            'list device groups' => ['GET', '/api/v0/devicegroups', 'get_device_groups', []],
            'device group by id' => ['GET', '/api/v0/devicegroups/12', 'get_devices_by_group', ['name' => '12']],
            'list port groups' => ['GET', '/api/v0/port_groups', 'get_port_groups', []],
            'assign port group' => ['POST', '/api/v0/port_groups/3/assign', 'assign_port_group', ['port_group_id' => '3']],
            'get port group' => ['GET', '/api/v0/port_groups/Uplinks%2FCore', 'get_ports_by_group', ['name' => 'Uplinks/Core']],
            'search oxidized' => ['GET', '/api/v0/oxidized/config/search/10.0.0.0%2F24', 'search_oxidized', ['searchstring' => '10.0.0.0/24']],
        ];
    }

    /**
     * @param  array<string, string>  $parameters
     */
    #[DataProvider('namedRoutes')]
    public function testNameWithSlashMatchesTheRoute(string $method, string $uri, string $name, array $parameters): void
    {
        $route = app('router')->getRoutes()->match(Request::create($uri, $method));

        $this->assertSame($name, $route->getName());
        $this->assertSame($parameters, $route->parameters());
    }

    public function testLocationWithSlashByName(): void
    {
        $headers = $this->headers();
        $location = Location::factory()->create(['location' => 'DC1/Room 2']);

        $this->getJson('/api/v0/location/DC1%2FRoom%202', $headers)
            ->assertStatus(200)
            ->assertJsonPath('get_location.id', $location->id);

        $this->deleteJson('/api/v0/locations/DC1%2FRoom%202', [], $headers)
            ->assertStatus(201)
            ->assertJsonPath('status', 'ok');

        $this->assertNull(Location::find($location->id));
    }

    public function testDeleteMissingLocationNamesIt(): void
    {
        $this->deleteJson('/api/v0/locations/DC1%2FRoom%203', [], $this->headers())
            ->assertStatus(400)
            ->assertJsonPath('message', 'Failed to delete DC1/Room 3 (Does not exists)');
    }

    public function testDeviceGroupWithSlashByName(): void
    {
        $headers = $this->headers();
        $device = Device::factory()->create();
        $group = DeviceGroup::factory()->create(['name' => 'Site A/Core', 'type' => 'static']);
        $group->devices()->attach($device);

        $this->getJson('/api/v0/devicegroups/Site%20A%2FCore', $headers)
            ->assertStatus(200)
            ->assertJsonPath('devices.0.device_id', $device->device_id);

        $this->deleteJson('/api/v0/devicegroups/Site%20A%2FCore/devices', ['devices' => [$device->device_id]], $headers)
            ->assertStatus(200);
        $this->assertSame(0, $group->devices()->count());

        $this->deleteJson('/api/v0/devicegroups/Site%20A%2FCore', [], $headers)
            ->assertStatus(200);
        $this->assertNull(DeviceGroup::find($group->id));
    }

    public function testAddOrRemoveGroupDevicesRequiresDevices(): void
    {
        $headers = $this->headers();
        $group = DeviceGroup::factory()->create(['name' => 'Core', 'type' => 'static']);
        $group->devices()->attach(Device::factory()->count(2)->create());

        // e.g. a delete meant for a group named "Core/devices" must not empty the group "Core"
        $this->deleteJson('/api/v0/devicegroups/Core%2Fdevices', [], $headers)
            ->assertStatus(422)
            ->assertJsonPath('message.devices.0', 'The devices field must be present.');
        $this->deleteJson('/api/v0/devicegroups/Core/devices', ['devices' => null], $headers)
            ->assertStatus(422)
            ->assertJsonPath('message.devices.0', 'The devices field must be a list.');
        $this->postJson('/api/v0/devicegroups/Core/devices', [], $headers)
            ->assertStatus(422)
            ->assertJsonPath('message.devices.0', 'The devices field must be present.');

        $server = $this->transformHeadersToServerVars($headers);
        foreach (['POST', 'DELETE'] as $method) {
            foreach (['', '0', 'abc', '1', 5, true, ['a' => 1]] as $invalid) {
                $this->json($method, '/api/v0/devicegroups/Core/devices', ['devices' => $invalid], $headers)
                    ->assertStatus(422)
                    ->assertJsonPath('message.devices.0', 'The devices field must be a list.');
            }
            // the handlers read the raw body, with or without a JSON content type
            $this->call($method, '/api/v0/devicegroups/Core/devices', [], [], [], $server, '{"devices":""}')
                ->assertStatus(422)
                ->assertJsonPath('message.devices.0', 'The devices field must be a list.');
        }

        $this->assertSame(2, $group->devices()->count());
    }

    public function testAddOrRemoveNoGroupDevicesIsANoOp(): void
    {
        $headers = $this->headers();
        $group = DeviceGroup::factory()->create(['name' => 'Core', 'type' => 'static']);
        $devices = Device::factory()->count(2)->create();
        $group->devices()->attach($devices);

        $this->deleteJson('/api/v0/devicegroups/Core/devices', ['devices' => []], $headers)
            ->assertStatus(200)
            ->assertJsonPath('message', 'Devices removed');
        $this->postJson('/api/v0/devicegroups/Core/devices', ['devices' => []], $headers)
            ->assertStatus(200)
            ->assertJsonPath('message', 'Devices added');

        $this->assertEqualsCanonicalizing($devices->modelKeys(), $group->devices()->pluck('devices.device_id')->all());
    }

    public function testDeviceGroupNameCannotEndInASubRoute(): void
    {
        $headers = $this->headers();

        foreach (['Core/devices', 'Core/maintenance'] as $name) {
            $this->postJson('/api/v0/devicegroups', ['name' => $name, 'type' => 'static', 'devices' => []], $headers)
                ->assertStatus(422)
                ->assertJsonPath('message.name.0', 'The name must not end in /devices or /maintenance.');
        }
        $this->assertSame(0, DeviceGroup::whereIn('name', ['Core/devices', 'Core/maintenance'])->count());

        $group = DeviceGroup::factory()->create(['name' => 'Core', 'type' => 'static']);
        $this->patchJson("/api/v0/devicegroups/$group->id", ['name' => 'Core/maintenance'], $headers)
            ->assertStatus(422)
            ->assertJsonPath('message.name.0', 'The name must not end in /devices or /maintenance.');
        $this->assertSame('Core', $group->fresh()?->name);
    }

    public function testAddDeviceGroupWithoutDescription(): void
    {
        $this->postJson('/api/v0/devicegroups', ['name' => 'Core/devices-old', 'type' => 'static', 'devices' => []], $this->headers())
            ->assertStatus(201);

        $this->assertSame('', DeviceGroup::where('name', 'Core/devices-old')->value('desc'));
    }

    public function testUpdateDeviceGroupWithItsCurrentName(): void
    {
        $headers = $this->headers();
        $group = DeviceGroup::factory()->create(['name' => 'Core', 'type' => 'static']);
        DeviceGroup::factory()->create(['name' => 'Edge', 'type' => 'static']);

        $this->patchJson('/api/v0/devicegroups/Core', ['name' => 'Core', 'desc' => 'Core switches'], $headers)
            ->assertStatus(200);
        $this->assertSame('Core switches', $group->fresh()?->desc);

        $this->patchJson('/api/v0/devicegroups/Core', ['name' => 'Edge'], $headers)
            ->assertStatus(422)
            ->assertJsonPath('message.name.0', 'The name has already been taken.');
    }

    public function testExistingGroupNamedLikeASubRoute(): void
    {
        $headers = $this->headers();
        $device = Device::factory()->create();
        $maintenance = DeviceGroup::factory()->create(['name' => 'Core/maintenance', 'type' => 'static']);
        $maintenance->devices()->attach($device);
        $devices = DeviceGroup::factory()->create(['name' => 'Core/devices', 'type' => 'static']);
        $devices->devices()->attach($device);

        $this->getJson('/api/v0/devicegroups/Core%2Fmaintenance', $headers)
            ->assertStatus(200)
            ->assertJsonPath('devices.0.device_id', $device->device_id);
        // keeping its current name is allowed
        $this->patchJson('/api/v0/devicegroups/Core%2Fmaintenance', ['name' => 'Core/maintenance', 'desc' => 'changed'], $headers)
            ->assertStatus(200);
        $this->assertSame('changed', $maintenance->fresh()?->desc);
        $this->deleteJson('/api/v0/devicegroups/Core%2Fmaintenance', [], $headers)
            ->assertStatus(200);
        $this->assertNull(DeviceGroup::find($maintenance->id));

        $this->getJson('/api/v0/devicegroups/Core%2Fdevices', $headers)
            ->assertStatus(200)
            ->assertJsonPath('devices.0.device_id', $device->device_id);
        // DELETE by name is the remove-devices route of "Core", so this one is deleted by id
        $this->deleteJson("/api/v0/devicegroups/$devices->id", [], $headers)
            ->assertStatus(200);
        $this->assertNull(DeviceGroup::find($devices->id));
    }

    public function testWrongMethodOnGroupSubRouteIsNotFound(): void
    {
        $headers = $this->headers();
        $group = DeviceGroup::factory()->create(['name' => 'Core', 'type' => 'static']);

        $requests = [
            ['GET', '/api/v0/devicegroups/Core/devices'],
            ['PATCH', '/api/v0/devicegroups/Core/devices'],
            ['GET', '/api/v0/devicegroups/Core/maintenance'],
            ['PATCH', '/api/v0/devicegroups/Core/maintenance'],
            ['DELETE', '/api/v0/devicegroups/Core/maintenance'],
        ];

        foreach ($requests as [$method, $uri]) {
            $this->json($method, $uri, ['name' => 'Renamed'], $headers)
                ->assertStatus(404)
                ->assertJsonPath('message', "This API route doesn't exist.");
        }
        $this->assertSame('Core', $group->fresh()?->name);

        // other unknown names keep the group not found message
        $this->getJson('/api/v0/devicegroups/Core%2FEdge', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device group not found');
        $this->deleteJson('/api/v0/devicegroups/Core%2FEdge', [], $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device group Core/Edge not found');
    }

    public function testPortGroupWithSlashByName(): void
    {
        $headers = $this->headers();
        $portGroup = PortGroup::factory()->create(['name' => 'Uplinks/Core']);

        $this->getJson('/api/v0/port_groups/Uplinks%2FCore', $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'No ports found in group Uplinks/Core');

        $port = Port::factory()->make();
        Device::factory()->create()->ports()->save($port);
        $portGroup->ports()->attach($port);

        $this->getJson('/api/v0/port_groups/Uplinks%2FCore', $headers)
            ->assertStatus(200)
            ->assertJsonPath('ports.0.port_id', $port->port_id);
    }

    /**
     * @return array<string, string>
     */
    private function headers(): array
    {
        /** @var User $user */
        $user = User::factory()->admin()->create();

        return ['X-Auth-Token' => $user->createToken('test')->plainTextToken];
    }
}
