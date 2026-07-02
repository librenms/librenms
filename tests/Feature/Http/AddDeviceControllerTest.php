<?php

namespace Tests\Feature\Http;

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

        $response = $this->actingAs($admin)->post(route('device.add.store'), [
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
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('hostname');

        $errors = session('errors')->get('hostname');
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

        // Mock PollingMethodFactory to return mocked methods
        $calledCredentials = [];
        $icmpMock = Mockery::mock(\LibreNMS\Interfaces\PollingMethod::class);
        $icmpMock->shouldReceive('isAvailable')->andReturn(true);

        $snmpMock = Mockery::mock(\LibreNMS\Interfaces\PollingMethod::class);
        $snmpMock->shouldReceive('isAvailable')
            ->andReturnUsing(function ($device) use (&$calledCredentials) {
                $snmpMethod = $device->pollingMethods->firstWhere('method_type', \LibreNMS\Enum\PollingMethodType::Snmp);
                $secret = $snmpMethod?->secret;
                if ($secret) {
                    $calledCredentials[] = $secret->data;
                }
                return false; // Force it to fail to collect all tried credentials
            });

        $factoryMock = Mockery::mock(\LibreNMS\Polling\PollingMethodFactory::class);
        $factoryMock->shouldReceive('make')
            ->andReturnUsing(fn($method) => match ($method->method_type) {
                \LibreNMS\Enum\PollingMethodType::Icmp => $icmpMock,
                \LibreNMS\Enum\PollingMethodType::Snmp => $snmpMock,
            });
        $this->instance(\LibreNMS\Polling\PollingMethodFactory::class, $factoryMock);

        // Set global configured SNMP credentials to something we shouldn't attempt
        \App\Facades\LibrenmsConfig::set('snmp.version', ['v2c', 'v3']);
        \App\Facades\LibrenmsConfig::set('snmp.community', ['global-community']);
        \App\Facades\LibrenmsConfig::set('snmp.v3', [
            ['authname' => 'global-v3-user', 'authlevel' => 'authNoPriv', 'authpass' => 'globalpass']
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

        $response = $this->actingAs($admin)->post(route('device.add.store'), [
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
                ]
            ]
        ]);

        // It should fail because SnmpIsAvailable always returned false, throwing HostUnreachableException
        $response->assertRedirect();
        $response->assertSessionHasErrors('hostname');

        // Verify that ONLY the 'target-community' was attempted, NOT 'global-community' or 'global-v3-user'
        $this->assertCount(1, $calledCredentials);
        $this->assertEquals('target-community', $calledCredentials[0]['community']);
    }
}
