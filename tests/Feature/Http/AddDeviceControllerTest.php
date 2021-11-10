<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class AddDeviceControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('device.create');
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    public function testStoreDeviceHostUnreachableReturnsMultipleErrors(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        // We overload ValidateDeviceAndCreate to throw HostUnreachableException
        $mock = \Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');

        $exception = new HostUnreachableException('Could not connect to laurens.rtr.ncn.net');
        $exception->addReason('SNMP v2c: No reply using credential "public"');
        $exception->addReason('SNMP v3: No reply using credential "root"');

        $mock->shouldReceive('execute')
            ->once()
            ->andThrow($exception);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'laurens.rtr.ncn.net',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '1',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['hostname']]);

        $errors = $response->json('errors.hostname');
        $this->assertCount(3, $errors);
        $this->assertStringContainsString('Could not connect to laurens.rtr.ncn.net', $errors[0]);
        $this->assertSame('SNMP v2c: No reply using credential "public"', $errors[1]);
        $this->assertSame('SNMP v3: No reply using credential "root"', $errors[2]);
    }

    public function testStoreDeviceWithExplicitCredentialsDoesNotAttemptDefaults(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $calledCredentials = [];

        // Mock Fping for ICMP check
        $fpingMock = Mockery::mock(\LibreNMS\Data\Source\Icmp\Fping::class);
        $statusMock = \LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp();
        $fpingMock->shouldReceive('ping')->andReturn($statusMock);
        $this->instance(\LibreNMS\Data\Source\Icmp\Fping::class, $fpingMock);

        // Mock the SNMP backend to capture tried credentials
        $backend = Mockery::mock(\LibreNMS\Data\Source\Snmp\SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, $config) use (&$calledCredentials) {
            $calledCredentials[] = ['community' => $config->community];

            return new \LibreNMS\Data\Source\Snmp\RawSnmpResponse('', 'Timeout', 1);
        });
        $this->instance(\LibreNMS\Data\Source\Snmp\SnmpBackendInterface::class, $backend);

        // Set global configured default credentials to something we shouldn't attempt
        $globalSecret = \App\Models\Secret::create([
            'description' => 'Global Default Secret',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => [
                'version' => 'v2c',
                'community' => 'global-community',
            ],
        ]);
        \App\Facades\LibrenmsConfig::set('snmp.default_credentials', [$globalSecret->id]);

        // Create an existing secret
        $secret = \App\Models\Secret::create([
            'description' => 'Target Secret',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => [
                'version' => 'v2c',
                'community' => 'target-community',
            ],
        ]);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'laurens.rtr.ncn.net',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '1',
                    'credential_mode' => 'existing',
                    'secret_id' => $secret->id,
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors' => ['hostname']]);

        // Verify that ONLY the 'target-community' was attempted, NOT 'global-community' or 'global-v3-user'
        $this->assertCount(1, $calledCredentials);
        $this->assertEquals('target-community', $calledCredentials[0]['community']);
    }

    public function testStoreDeviceWithPortAssociationModeInSnmpSettings(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $mock->shouldReceive('execute')->once()->andReturnUsing(fn () => true);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'test-device.example.com',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '0',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                        'port_association_mode' => 'ifName',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['status', 'message', 'redirect']);
        $this->assertEquals('ok', $response->json('status'));
    }

    public function testStoreDeviceJsonReturnsJsonResponseOnSuccess(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $mock->shouldReceive('execute')->once()->andReturn(true);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'json-device.example.com',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '0',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['status', 'message', 'redirect']);
        $this->assertEquals('ok', $response->json('status'));
    }

    public function testStoreDeviceJsonHostUnreachableReturnsJsonErrors(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $exception = new HostUnreachableException('Could not connect to json-unreachable.example.com');
        $exception->addReason('SNMP v2c: No reply using credential "public"');

        $mock->shouldReceive('execute')
            ->once()
            ->andThrow($exception);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'json-unreachable.example.com',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '1',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['status', 'message', 'error_details', 'errors' => ['hostname']]);
        $this->assertEquals('unreachable', $response->json('status'));
        $this->assertSame('SNMP v2c: No reply using credential "public"', $response->json('error_details'));
        $this->assertStringContainsString('Could not connect to json-unreachable.example.com', $response->json('errors.hostname.0'));
    }

    public function testStoreDeviceWithForceAddSucceeds(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $capturedForce = null;
        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $mock->shouldReceive('__construct')
            ->andReturnUsing(function ($device, $methods, $force) use (&$capturedForce): void {
                $capturedForce = $force;
            });
        $mock->shouldReceive('execute')->once()->andReturn(true);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'force-add.example.com',
            'poller_group' => 0,
            'force_add' => 1,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '1',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertTrue($capturedForce);
    }

    public function testStoreDeviceWithoutPollingMethodsReturnsCustomErrorMessage(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'test-device.example.com',
            'poller_group' => 0,
            'polling_methods' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'polling_methods' => 'At least one polling method is required',
        ]);
    }

    public function testIndexProvidesDefaultDisplayTemplate(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        \App\Facades\LibrenmsConfig::set('device_display_default', '{{ $hostname }} - {{ $sysName }}');

        $response = $this->actingAs($admin)->get(route('device.add'));

        $response->assertOk();
        $response->assertViewHas('default_display_template', '{{ $hostname }} - {{ $sysName }}');
        $response->assertSee('name="display_template"', false);
        $response->assertSee('id="secret-select-snmp"', false);
        $response->assertSee('name="polling_methods[snmp][secret_id]"', false);
        $response->assertSee('id="os-select"', false);
    }

    public function testStoreDeviceWithDisplayTemplate(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $capturedDevice = null;
        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $mock->shouldReceive('__construct')
            ->andReturnUsing(function ($device) use (&$capturedDevice): void {
                $capturedDevice = $device;
            });
        $mock->shouldReceive('execute')->once()->andReturn(true);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'display-test.example.com',
            'display_template' => '{{ $hostname }} ({{ $ip }})',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '0',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertNotNull($capturedDevice);
        $this->assertEquals('{{ $hostname }} ({{ $ip }})', $capturedDevice->display_template);
    }

    public function testStoreDeviceWithInvalidDisplayTemplateLengthFails(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'display-test.example.com',
            'display_template' => str_repeat('a', 129),
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '0',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['display_template']);
    }

    public function testStoreDeviceWithEmptyPollingSettingsFallsBackToDefaults(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $mock->shouldReceive('execute')->once()->andReturnUsing(fn () => true);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'fallback-defaults.example.com',
            'poller_group' => 0,
            'polling_methods' => [
                'snmp' => [
                    'active' => '1',
                    'validate' => '0',
                    'credential_mode' => 'default',
                    'settings' => [
                        'transport' => 'udp',
                        'port' => '',
                        'timeout' => '',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertEquals('ok', $response->json('status'));
    }

    public function testDetectCredentialsAttemptsDefaultCredentialsInOrderAndAssociatesWinningSecret(): void
    {
        $secret1 = \App\Models\Secret::create([
            'description' => 'Default SNMP 1',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => [
                'version' => 'v2c',
                'community' => 'wrong-comm',
            ],
        ]);
        $secret2 = \App\Models\Secret::create([
            'description' => 'Default SNMP 2',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => [
                'version' => 'v2c',
                'community' => 'correct-comm',
            ],
        ]);

        \App\Facades\LibrenmsConfig::set('snmp.default_credentials', [$secret1->id, $secret2->id]);

        $fpingMock = Mockery::mock(\LibreNMS\Data\Source\Icmp\Fping::class);
        $statusMock = \LibreNMS\Data\Source\Icmp\FpingResponse::artificialUp();
        $fpingMock->shouldReceive('ping')->andReturn($statusMock);
        $this->instance(\LibreNMS\Data\Source\Icmp\Fping::class, $fpingMock);

        $triedCommunities = [];
        $backend = Mockery::mock(\LibreNMS\Data\Source\Snmp\SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, $config) use (&$triedCommunities) {
            $comm = $config->community;
            $triedCommunities[] = $comm;

            return $comm === 'correct-comm'
                ? new \LibreNMS\Data\Source\Snmp\RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = OID: SNMPv2-SMI::enterprises.9.1.1', '', 0)
                : new \LibreNMS\Data\Source\Snmp\RawSnmpResponse('', 'Timeout', 1);
        });
        $this->instance(\LibreNMS\Data\Source\Snmp\SnmpBackendInterface::class, $backend);

        $device = new Device([
            'hostname' => 'detect-test.example.com',
        ]);

        $action = new \App\Actions\Device\ValidateDeviceAndCreate($device, force: false, ping_fallback: false);
        $result = $action->execute();

        $this->assertTrue($result);
        $this->assertEquals(['wrong-comm', 'correct-comm'], array_values(array_unique($triedCommunities)));
        $snmpMethod = $device->pollingMethods->firstWhere('method_type', \LibreNMS\Enum\PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $this->assertEquals($secret2->id, $snmpMethod->secret_id);
    }

    public function testStoreDeviceWithNoActiveMethodsIsRejected(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => '127.0.0.11',
            'poller_group' => 0,
            'polling_methods' => ['snmp' => ['active' => 0]],
        ]);

        $response->assertStatus(422)->assertJsonStructure(['errors' => ['polling_methods']]);
        $this->assertDatabaseMissing('devices', ['hostname' => '127.0.0.11']);
    }

    public function testStoreDeviceDoesNotLeakUnexpectedExceptionMessages(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $mock = Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');
        $mock->shouldReceive('execute')->andThrow(new \RuntimeException('SQLSTATE[23000] secret details'));

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => '127.0.0.12',
            'poller_group' => 0,
            'force_add' => 1,
            'polling_methods' => ['icmp' => ['active' => 1]],
        ]);

        $response->assertStatus(422);
        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
    }

    public function testDefaultSecretDescriptionIsUniqueWhenOneExists(): void
    {
        \App\Models\Secret::create([
            'description' => 'SNMP 127.0.0.10',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'x'],
        ]);

        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('device.create');

        $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => '127.0.0.10',
            'poller_group' => 0,
            'polling_methods' => ['snmp' => [
                'active' => 1, 'validate' => 0, 'credential_mode' => 'new', 'description' => '',
                'secret_data' => ['version' => 'v2c', 'community' => 'y'],
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('secrets', ['description' => 'SNMP 127.0.0.10 (2)']);
    }
}
