<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class ServicesApiTest extends DBTestCase
{
    use DatabaseTransactions;

    private string $pluginDir;

    protected function setUp(): void
    {
        parent::setUp();

        // add_service_for_host only accepts types with a check_<type> plugin
        $this->pluginDir = sys_get_temp_dir() . '/librenms-plugins-' . uniqid();
        mkdir($this->pluginDir);
        touch($this->pluginDir . '/check_icmp');
        LibrenmsConfig::set('nagios_plugins', $this->pluginDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->pluginDir);

        parent::tearDown();
    }

    public function testAddServiceWithoutOptionalFieldsUsesPollerTarget(): void
    {
        $device = Device::factory()->create(['hostname' => 'svc-host.example.com', 'overwrite_ip' => null]);

        $response = $this->postJson("/api/v0/services/$device->hostname", ['type' => 'icmp'], $this->headers())
            ->assertStatus(201)
            ->assertJsonPath('status', 'ok');

        $service = Service::where('device_id', $device->device_id)->sole();
        $response->assertJsonPath('message', "Service icmp has been added to device svc-host.example.com (#$service->service_id)");
        $this->assertSame('icmp', $service->service_type);
        $this->assertSame('svc-host.example.com', $service->service_ip);
        $this->assertSame('', $service->service_desc);
        $this->assertSame('', $service->service_param);
        $this->assertEquals(0, $service->service_ignore);
        $this->assertEquals(0, $service->service_disabled);
    }

    public function testAddServiceKeepsGivenFields(): void
    {
        $device = Device::factory()->create();

        $response = $this->postJson("/api/v0/services/$device->device_id", [
            'type' => 'icmp',
            'ip' => '192.0.2.10',
            'desc' => 'ping check',
            'param' => '-c 5',
            'ignore' => 1,
            'disable' => true,
            'name' => 'Ping',
        ], $this->headers())->assertStatus(201);

        $service = Service::where('device_id', $device->device_id)->sole();
        $response->assertJsonPath('message', "Service icmp has been added to device $device->device_id (#$service->service_id)");
        $this->assertSame('192.0.2.10', $service->service_ip);
        $this->assertSame('ping check', $service->service_desc);
        $this->assertSame('-c 5', $service->service_param);
        $this->assertSame('Ping', $service->service_name);
        $this->assertEquals(1, $service->service_ignore);
        $this->assertEquals(1, $service->service_disabled);
    }

    public function testAddServiceForUnknownDeviceIsNotFound(): void
    {
        $headers = $this->headers();

        $this->postJson('/api/v0/services/does-not-exist.example.com', ['type' => 'icmp'], $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device does-not-exist.example.com does not exist');

        $this->postJson('/api/v0/services/999999', ['type' => 'icmp'], $headers)
            ->assertStatus(404)
            ->assertJsonPath('message', 'Device 999999 does not exist');

        $this->assertSame(0, Service::count());
    }

    public function testAddServiceRequiresAccessToTheDevice(): void
    {
        LibrenmsConfig::set('show_services', true);
        Role::findOrCreate('user');
        Permission::findOrCreate('service.create');
        $owned = Device::factory()->create();
        $other = Device::factory()->create();

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('service.create');
        $user->devicesOwned()->attach($owned->device_id);
        $headers = ['X-Auth-Token' => $user->createToken('test')->plainTextToken];

        $this->postJson("/api/v0/services/$other->device_id", ['type' => 'icmp'], $headers)
            ->assertStatus(403)
            ->assertJsonPath('message', 'Insufficient permissions to access this device');
        $this->assertSame(0, Service::where('device_id', $other->device_id)->count());

        $this->postJson("/api/v0/services/$owned->device_id", ['type' => 'icmp'], $headers)
            ->assertStatus(201);
        $this->assertSame(1, Service::where('device_id', $owned->device_id)->count());
    }

    public function testAddServiceRejectsInvalidJson(): void
    {
        $device = Device::factory()->create();
        $headers = $this->headers();

        $this->postRaw("/api/v0/services/$device->device_id", '{"type": "icmp"', $headers)
            ->assertStatus(400)
            ->assertJsonPath('message', "We couldn't parse the provided json. Syntax error");

        // valid json, but not an object
        $this->postRaw("/api/v0/services/$device->device_id", '"icmp"', $headers)
            ->assertStatus(400)
            ->assertJsonPath('message', 'The JSON body must be an object');

        $this->assertSame(0, Service::count());
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidFieldProvider(): array
    {
        return [
            'type array' => [['type' => ['icmp']], 'The type field must be a string'],
            'type bool' => [['type' => true], 'The type field must be a string'],
            'desc array' => [['type' => 'icmp', 'desc' => ['x']], 'The desc field must be a string or number'],
            'ip object' => [['type' => 'icmp', 'ip' => new \stdClass], 'The ip field must be a string or number'],
            'ip bool' => [['type' => 'icmp', 'ip' => false], 'The ip field must be a string or number'],
            'name bool' => [['type' => 'icmp', 'name' => true], 'The name field must be a string or number'],
            'ignore string' => [['type' => 'icmp', 'ignore' => 'abc'], 'The ignore field must be a boolean'],
            'ignore int' => [['type' => 'icmp', 'ignore' => 2], 'The ignore field must be a boolean'],
            'disable array' => [['type' => 'icmp', 'disable' => [1]], 'The disable field must be a boolean'],
            'name too long' => [['type' => 'icmp', 'name' => str_repeat('n', 256)], 'The name field must not be longer than 255 characters'],
            'desc too long' => [['type' => 'icmp', 'desc' => str_repeat('d', 65536)], 'The desc field must not be longer than 65535 bytes'],
            'param too long' => [['type' => 'icmp', 'param' => str_repeat('p', 65536)], 'The param field must not be longer than 65535 bytes'],
            'ip too long' => [['type' => 'icmp', 'ip' => str_repeat('i', 65536)], 'The ip field must not be longer than 65535 bytes'],
        ];
    }

    /**
     * @param  array<string, mixed>  $body
     */
    #[DataProvider('invalidFieldProvider')]
    public function testAddServiceRejectsInvalidFields(array $body, string $message): void
    {
        $device = Device::factory()->create();

        $this->postJson("/api/v0/services/$device->device_id", $body, $this->headers())
            ->assertStatus(400)
            ->assertJsonPath('message', $message);

        $this->assertSame(0, Service::count());
    }

    public function testAddServiceAcceptsNumbersAndMaxLengths(): void
    {
        $device = Device::factory()->create();
        $headers = $this->headers();
        $url = "/api/v0/services/$device->device_id";

        $this->postJson($url, ['type' => 'icmp', 'desc' => 5, 'param' => 1.5], $headers)->assertStatus(201);
        $service = Service::where('device_id', $device->device_id)->sole();
        $this->assertSame('5', $service->service_desc);
        $this->assertSame('1.5', $service->service_param);
        $service->delete();

        $this->postJson($url, [
            'type' => 'icmp',
            'name' => str_repeat('n', 255),
            'desc' => str_repeat('d', 65535),
        ], $headers)->assertStatus(201);
        $service = Service::where('device_id', $device->device_id)->sole();
        $this->assertSame(255, strlen((string) $service->service_name));
        $this->assertSame(65535, strlen((string) $service->service_desc));
    }

    /**
     * @return array<string, array{0: mixed, 1: int}>
     */
    public static function booleanFlagProvider(): array
    {
        return [
            'false' => [false, 0],
            'string false' => ['false', 0],
            'string 0' => ['0', 0],
            'int 0' => [0, 0],
            'null' => [null, 0],
            'true' => [true, 1],
            'string true' => ['true', 1],
            'int 1' => [1, 1],
            'string 1' => ['1', 1],
        ];
    }

    #[DataProvider('booleanFlagProvider')]
    public function testAddServiceParsesBooleanFlags(mixed $value, int $expected): void
    {
        $device = Device::factory()->create();

        $this->postJson("/api/v0/services/$device->device_id", [
            'type' => 'icmp',
            'ignore' => $value,
            'disable' => $value,
        ], $this->headers())->assertStatus(201);

        $service = Service::where('device_id', $device->device_id)->sole();
        $this->assertEquals($expected, $service->service_ignore);
        $this->assertEquals($expected, $service->service_disabled);
    }

    public function testAddServiceRejectsUnknownType(): void
    {
        $device = Device::factory()->create();

        $this->postJson("/api/v0/services/$device->device_id", ['type' => 'nope'], $this->headers())
            ->assertStatus(400)
            ->assertJsonPath('message', "The service nope does not exist.\n Available service types: icmp");

        $this->assertSame(0, Service::count());
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

    /**
     * @param  array<string, string>  $headers
     * @return TestResponse<\Illuminate\Http\Response>
     */
    private function postRaw(string $uri, string $body, array $headers): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers + [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        return $this->call('POST', $uri, [], [], [], $server, $body);
    }
}
