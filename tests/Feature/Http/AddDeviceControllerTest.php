<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\User;
use LibreNMS\Exceptions\HostUnreachableSnmpException;
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

        // We overload ValidateDeviceAndCreate to throw HostUnreachableSnmpException
        $mock = \Mockery::mock('overload:App\Actions\Device\ValidateDeviceAndCreate');

        $exception = new HostUnreachableSnmpException('laurens.rtr.ncn.net');
        $exception->addReason('v2c', 'public');
        $exception->addReason('v3', 'root/noAuthNoPriv');

        $mock->shouldReceive('execute')
            ->once()
            ->andThrow($exception);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'laurens.rtr.ncn.net',
            'poller_group' => 0,
            'port_assoc_mode' => 'ifIndex',
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

                $queryMock = Mockery::mock(\LibreNMS\Data\Source\SnmpQueryInterface::class);
                $queryMock->shouldReceive('get')->andReturn(new \LibreNMS\Data\Source\SnmpResponse('', '', 1));

                return $queryMock;
            });

        // Set global configured SNMP credentials to something we shouldn't attempt
        \App\Facades\LibrenmsConfig::set('snmp.version', ['v2c', 'v3']);
        \App\Facades\LibrenmsConfig::set('snmp.community', ['global-community']);
        \App\Facades\LibrenmsConfig::set('snmp.v3', [
            ['authname' => 'global-v3-user', 'authlevel' => 'authNoPriv', 'authpass' => 'globalpass'],
        ]);

        // Create an existing secret
        $secret = \App\Models\Secret::create([
            'description' => 'Target Secret',
            'secret_type' => \LibreNMS\Enum\SecretType::Snmp,
            'default' => false,
            'data' => [
                'version' => 'v2c',
                'community' => 'target-community',
            ],
        ]);

        $response = $this->actingAs($admin)->postJson(route('device.add.store'), [
            'hostname' => 'laurens.rtr.ncn.net',
            'poller_group' => 0,
            'port_assoc_mode' => 'ifIndex',
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
        $mock->shouldReceive('execute')->once()->andReturnUsing(function () {
            return true;
        });

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
        $exception = new HostUnreachableSnmpException('json-unreachable.example.com');
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
        $response->assertJsonStructure(['message', 'errors' => ['hostname']]);
        $this->assertStringContainsString('Could not connect to json-unreachable.example.com', $response->json('errors.hostname.0'));
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
}
