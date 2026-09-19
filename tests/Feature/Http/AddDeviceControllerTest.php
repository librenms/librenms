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
        $exception->addReason('v2c', 'public');
        $exception->addReason('v3', 'root/noAuthNoPriv');

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
        $this->assertStringContainsString('SNMP v2c: No reply with community public', $errors[1]);
        $this->assertStringContainsString('SNMP v3: No reply with credentials root/noAuthNoPriv', $errors[2]);
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

        // Mock SnmpQuery to capture tried credentials
        \SnmpQuery::partialMock()->shouldReceive('device')
            ->andReturnUsing(function ($device) use (&$calledCredentials) {
                $snmpMethod = $device?->pollingMethods->firstWhere('method_type', \LibreNMS\Enum\PollingMethodType::Snmp);
                $secret = $snmpMethod?->secret;
                if ($secret) {
                    $calledCredentials[] = $secret->data;
                }

                $queryMock = Mockery::mock(\LibreNMS\Data\Source\Snmp\SnmpQueryInterface::class);
                $queryMock->shouldReceive('get')->andReturn(new \LibreNMS\Data\Source\Snmp\SnmpResponse([], '', 1));

                return $queryMock;
            });

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
        $exception->addReason('v2c', 'public');

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
        $this->assertStringContainsString('SNMP v2c: No reply with community public', $response->json('error_details'));
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
            ->andReturnUsing(function ($device, $force) use (&$capturedForce): void {
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

        $triedCommunities = [];
        \SnmpQuery::partialMock()->shouldReceive('device')
            ->andReturnUsing(function ($device) use (&$triedCommunities) {
                $snmpMethod = $device?->pollingMethods->firstWhere('method_type', \LibreNMS\Enum\PollingMethodType::Snmp);
                $comm = $snmpMethod?->secret?->data['community'] ?? null;
                $triedCommunities[] = $comm;

                $queryMock = Mockery::mock(\LibreNMS\Data\Source\Snmp\SnmpQueryInterface::class);
                if ($comm === 'correct-comm') {
                    $queryMock->shouldReceive('get')->andReturn(new \LibreNMS\Data\Source\Snmp\SnmpResponse(['.1.3.6.1.2.1.1.1.0' => 'Test System'], '', 0));
                } else {
                    $queryMock->shouldReceive('get')->andReturn(new \LibreNMS\Data\Source\Snmp\SnmpResponse([], 'Timeout', 1));
                }

                return $queryMock;
            });

        $device = new Device([
            'hostname' => 'detect-test.example.com',
        ]);

        $action = new \App\Actions\Device\ValidateDeviceAndCreate($device, force: false, ping_fallback: false);
        $result = $action->execute();

        $this->assertTrue($result);
        $this->assertEquals(['wrong-comm', 'correct-comm'], $triedCommunities);
        $snmpMethod = $device->pollingMethods->firstWhere('method_type', \LibreNMS\Enum\PollingMethodType::Snmp);
        $this->assertNotNull($snmpMethod);
        $this->assertEquals($secret2->id, $snmpMethod->secret_id);
    }
}
