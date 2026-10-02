<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\UnixAgentConfig;
use LibreNMS\Polling\Method\Methods\IcmpPollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Polling\Secrets\Definitions\SecretDefinition;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class EditPollingControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('device.update');
        Permission::findOrCreate('secret.create');
        Permission::findOrCreate('secret.update');
        Permission::findOrCreate('secret.view');
        Permission::findOrCreate('secret.delete');
        Permission::findOrCreate('secret.unmask');
    }

    public function testUpdateSnmpPollingMethodUpdatesPortAssociationMode(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();

        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'settings' => [
                'transport' => 'udp',
                'port' => 161,
                'timeout' => 1,
                'retries' => 0,
                'max_repeaters' => 0,
                'max_oid' => 10,
                'port_association_mode' => 'ifIndex',
            ],
        ]);

        $response = $this->actingAs($admin)->put(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifName',
                ],
            ]
        );

        $response->assertRedirect();
        $this->assertEquals('ifName', $device->fresh()->pollingMethod(PollingMethodType::Snmp)->settings['port_association_mode']);
        $this->assertEquals('ifName', $device->fresh()->polling()->snmp()->portAssociationMode);
    }

    public function testStorePollingMethodRejectsDuplicateDefaultSecretDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'test-device.example.com']);

        // Pre-create a secret that has the exact default description
        Secret::create([
            'description' => 'SNMP test-device.example.com',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(
            route('device.edit.polling.store', ['device' => $device]),
            [
                'method_type' => 'snmp',
                'secret_mode' => 'new',
                'description' => 'SNMP test-device.example.com',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'private',
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertSessionHasErrors(['description']);
    }

    public function testStorePollingMethodRejectsDuplicateCustomSecretDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'test-device2.example.com']);

        Secret::create([
            'description' => 'Existing Custom Description',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(
            route('device.edit.polling.store', ['device' => $device]),
            [
                'method_type' => 'snmp',
                'secret_mode' => 'new',
                'description' => 'Existing Custom Description',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'private',
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertSessionHasErrors(['description']);
    }

    public function testIndexRendersPollingViewWithoutSecretData(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'SNMP Secret 123',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $device = Device::factory()->create(['hostname' => 'test-device.example.com']);

        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $secret->id,
            'enabled' => true,
        ]);

        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device]));
        $response->assertOk();
        $response->assertSee('SNMP Secret 123');
        $response->assertViewHas('allMethods', function ($allMethods) use ($secret) {
            $snmp = $allMethods->firstWhere('type', 'snmp');
            $this->assertSame(['id' => $secret->id, 'description' => 'SNMP Secret 123'], $snmp['secret']);

            return true;
        });
        $response->assertDontSee('"community"', false); // secret data is loaded on demand
    }

    public function testIndexRendersEachSecretOptionAndIdOnce(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $secret = Secret::create([
            'description' => 'SNMP Secret 456',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);
        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $secret->id,
        ]);

        $html = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device]))->assertOk()->getContent();

        // the edit and new secret forms are both on the page
        preg_match_all('/\sid="([^"]+)"/', $html, $ids);
        $this->assertSame([], array_keys(array_filter(array_count_values($ids[1]), fn (int $count): bool => $count > 1)), 'duplicate element ids');

        // the selected secret is rendered as a selected option, select2 must not add it again
        $this->assertStringNotContainsString('"text":"SNMP Secret 456"', $html);
    }

    public function testIndexRendersEmptyTabsWhenNoMethodsConfigured(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device]));

        $response->assertOk();
        $response->assertSee('No Polling Methods Configured');
        $response->assertSee(route('device.edit.polling.store', $device), false);
        $response->assertViewHas('tabsConfig', function (array $config): bool {
            $this->assertSame('', $config['initialTab']);
            $this->assertSame([], $config['activeMethods']);
            $this->assertCount(count(PollingMethodType::cases()), $config['allTypes']);

            return true;
        });
    }

    public function testIndexTabsConfigDefaultsToEnabledSnmp(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'last_check_successful' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device]));

        $response->assertOk();
        $response->assertViewHas('tabsConfig', function (array $config): bool {
            $this->assertSame('snmp', $config['initialTab']);
            $this->assertEqualsCanonicalizing(['snmp', 'icmp'], $config['activeMethods']);
            $this->assertSame(['configured' => true, 'enabled' => true, 'affectsAvailability' => true, 'lastCheckSuccessful' => false], $config['methods']['snmp']);
            $this->assertFalse($config['methods']['ipmi']['configured']);
            $this->assertContains(['type' => 'ipmi', 'label' => PollingMethodType::Ipmi->label()], $config['allTypes']);

            return true;
        });
    }

    public function testIndexTabsConfigFallsBackWhenSnmpDisabled(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => false,
        ]);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device, 'tab' => 'invalid']));

        $response->assertOk();
        $response->assertViewHas('tabsConfig', function (array $config): bool {
            $this->assertSame('icmp', $config['initialTab']); // first configured method
            $this->assertSame(['icmp', 'snmp'], $config['activeMethods']);

            return true;
        });
    }

    public function testIndexTabsConfigOpensRequestedUnconfiguredTab(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device, 'tab' => 'ipmi']));

        $response->assertOk();
        $response->assertViewHas('tabsConfig', function (array $config): bool {
            $this->assertSame('ipmi', $config['initialTab']);
            $this->assertSame(['icmp', 'ipmi'], $config['activeMethods']);
            $this->assertFalse($config['methods']['ipmi']['configured']);

            return true;
        });
    }

    public function testUpdateReturnsJsonResponseWhenRequested(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'icmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'settings' => [],
            ]
        );

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'message' => __('poller.method_updated'),
        ]);
        $response->assertJsonPath('method.type', 'icmp');
        $response->assertJsonPath('method.affects_availability', true);
        $response->assertSessionMissing('toasts'); // the page shows the message itself
    }

    public function testUpdateFlashesToastWhenRedirecting(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->put(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'icmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'settings' => [],
            ]
        );

        $response->assertRedirect(route('device.edit.polling', ['device' => $device, 'tab' => 'icmp']));
        $response->assertSessionHas('toasts', fn (array $toasts): bool => $toasts[0]['message'] === __('poller.method_updated'));
    }

    public function testDestroyReturnsJsonResponseWhenRequested(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::UnixAgent,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->deleteJson(
            route('device.edit.polling.destroy', ['device' => $device, 'methodType' => 'unix-agent'])
        );

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'message' => __('poller.method_removed'),
        ]);
        $response->assertSessionMissing('toasts'); // the page shows the message itself
    }

    public function testRemovingTheLastMethodMarksTheDeviceUp(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['status' => 0, 'status_reason' => 'icmp']);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
            'last_check_successful' => false,
        ]);

        $this->actingAs($admin)->deleteJson(
            route('device.edit.polling.destroy', ['device' => $device, 'methodType' => 'icmp'])
        )->assertOk();

        $device->refresh();
        $this->assertTrue($device->pollingMethods()->doesntExist());
        $this->assertEquals(1, $device->status); // nothing affects availability
        $this->assertSame('', $device->status_reason);
    }

    public function testUpdateSecretDataPersistsToDatabase(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Original SNMP Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $secret->id,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'secret_id' => (string) $secret->id,
                'secret_mode' => 'edit',
                'description' => 'Updated SNMP Secret',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'supersecret',
                    'port' => 161,
                    'retries' => 0,
                    'timeout' => 1,
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertOk();
        $this->assertEquals('Updated SNMP Secret', $secret->fresh()->description);
        $this->assertEquals('supersecret', $secret->fresh()->data['community']);
    }

    public function testUpdateWithNewSecretKeepsSharedSecret(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $originalSecret = Secret::create([
            'description' => 'Shared SNMP Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $device = Device::factory()->create();
        $pollingMethod = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $originalSecret->id,
            'enabled' => true,
        ]);

        $otherDevice = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $otherDevice->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $originalSecret->id,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'secret_id' => (string) $originalSecret->id,
                'secret_mode' => 'new',
                'description' => 'New Dedicated Secret',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'brandnew',
                    'port' => 161,
                    'retries' => 0,
                    'timeout' => 1,
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertOk();
        $this->assertEquals('public', $originalSecret->fresh()->data['community']);
        $newSecretId = $pollingMethod->fresh()->secret_id;
        $this->assertNotEquals($originalSecret->id, $newSecretId);
        $newSecret = Secret::find($newSecretId);
        $this->assertEquals('New Dedicated Secret', $newSecret->description);
        $this->assertEquals('brandnew', $newSecret->data['community']);
    }

    public function testUpdateWithNewSecretRejectsDuplicateDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $originalSecret = Secret::create([
            'description' => 'Shared SNMP Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $originalSecret->id,
            'enabled' => true,
        ]);

        $otherDevice = Device::factory()->create();
        DevicePollingMethod::factory()->create([
            'device_id' => $otherDevice->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $originalSecret->id,
            'enabled' => true,
        ]);

        // Attempting to create a new secret with the exact same description as the existing one
        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'secret_id' => (string) $originalSecret->id,
                'secret_mode' => 'new',
                'description' => 'Shared SNMP Secret',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'newcommunity',
                    'port' => 161,
                    'retries' => 0,
                    'timeout' => 1,
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function testEditSecretWithSameDescriptionUpdatesInPlace(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Solo SNMP Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $device = Device::factory()->create();
        $pollingMethod = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $secret->id,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'secret_id' => (string) $secret->id,
                'secret_mode' => 'edit',
                'description' => 'Solo SNMP Secret',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => 'updated_community',
                    'port' => 161,
                    'retries' => 0,
                    'timeout' => 1,
                ],
                'settings' => [
                    'transport' => 'udp',
                    'port' => 161,
                    'timeout' => 1,
                    'retries' => 0,
                    'max_repeaters' => 0,
                    'max_oid' => 10,
                    'port_association_mode' => 'ifIndex',
                ],
            ]
        );

        $response->assertOk();
        $this->assertEquals($secret->id, $pollingMethod->fresh()->secret_id);
        $this->assertEquals('updated_community', $secret->fresh()->data['community']);
        $this->assertEquals('Solo SNMP Secret', $secret->fresh()->description);
    }

    public function testUpdatePollingMethodUnreachableReturns422WithDetails(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'unreachable-device.invalid']);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'icmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'settings' => [],
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'unreachable',
            'message' => __('poller.reachability_failed', [
                'hostname' => 'unreachable-device.invalid',
                'method' => 'ICMP',
            ]),
        ]);
    }

    public function testUpdatePollingMethodForceSaveBypassesUnreachable(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'unreachable-device.invalid']);
        $method = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => false,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'icmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'settings' => [],
            ]
        );

        $response->assertOk();
        $this->assertTrue($method->fresh()->affects_availability);
        $this->assertNull($method->fresh()->last_check_successful);
    }

    public function testUpdateAvailabilityChangeIsLogged(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['status' => false, 'status_reason' => 'icmp']);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
            'last_check_successful' => false,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'icmp']),
            [
                'enabled' => '1',
                'affects_availability' => '0',
                'force_save' => '1',
                'settings' => [],
            ]
        );

        $response->assertOk();
        $this->assertTrue($device->fresh()->status);
        $this->assertDatabaseHas('eventlog', [
            'device_id' => $device->device_id,
            'message' => 'Device status changed to Up from icmp check.',
        ]);
    }

    public function testUpdateCannotRestoreMaskedValuesFromInaccessibleSecret(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(['device.update', 'secret.update']);

        $foreignSecret = Secret::create([
            'description' => 'Foreign SNMP Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'foreign-community'],
        ]);
        DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $foreignSecret->id,
        ]);

        $device = Device::factory()->create();
        $method = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => null,
        ]);

        $response = $this->actingAs($user)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']),
            [
                'enabled' => '1',
                'secret_id' => (string) $foreignSecret->id,
                'secret_mode' => 'new',
                'description' => 'Stolen',
                'secret_data' => [
                    'version' => 'v2c',
                    'community' => SecretDefinition::MASK,
                ],
            ]
        );

        $response->assertNotFound();
        $this->assertNull($method->fresh()->secret_id);
        $this->assertDatabaseMissing('secrets', ['description' => 'Stolen']);
    }

    public function testStorePollingMethodUnreachableReturns422(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'unreachable-device2.invalid']);

        $response = $this->actingAs($admin)->postJson(
            route('device.edit.polling.store', ['device' => $device]),
            [
                'method_type' => 'icmp',
                'settings' => [],
            ]
        );

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'unreachable',
            'message' => __('poller.reachability_failed', [
                'hostname' => 'unreachable-device2.invalid',
                'method' => 'ICMP',
            ]),
        ]);
        $this->assertDatabaseMissing('device_polling_methods', [
            'device_id' => $device->device_id,
            'method_type' => 'icmp',
        ]);
    }

    public function testStorePollingMethodForceSaveBypassesUnreachable(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'unreachable-device3.invalid']);

        $response = $this->actingAs($admin)->postJson(
            route('device.edit.polling.store', ['device' => $device]),
            [
                'method_type' => 'icmp',
                'force_save' => '1',
                'settings' => [],
            ]
        );

        $response->assertOk();
        $response->assertJsonPath('message', __('poller.method_added'));
        $response->assertSessionMissing('toasts'); // the page shows the message itself
        $this->assertDatabaseHas('device_polling_methods', [
            'device_id' => $device->device_id,
            'method_type' => 'icmp',
            'last_check_successful' => null,
        ]);
    }

    public function testUpdatePollingMethodSettingsOnlyStoresOverridesAndFallsBackToDefaults(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();

        $method = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::UnixAgent,
            'enabled' => true,
            'settings' => [],
        ]);

        // 1. Update with empty port/timeout (should store empty array, not fallback values)
        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'unix-agent']),
            [
                'enabled' => '1',
                'affects_availability' => '0',
                'force_save' => '1',
                'settings' => [
                    'port' => '',
                    'timeout' => '',
                ],
            ]
        );
        $response->assertOk();

        $freshMethod = $method->fresh();
        $this->assertEquals([], $freshMethod->settings);
        $config = $device->fresh()->polling()->unixAgent();
        $this->assertInstanceOf(UnixAgentConfig::class, $config);
        $this->assertEquals(6556, $config->port);
        $this->assertEquals(10, $config->timeout);

        // 2. Update with custom port override
        $response2 = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'unix-agent']),
            [
                'enabled' => '1',
                'affects_availability' => '0',
                'force_save' => '1',
                'settings' => [
                    'port' => 6557,
                    'timeout' => '',
                ],
            ]
        );
        $response2->assertOk();

        $freshMethod = $method->fresh();
        $this->assertEquals(['port' => 6557], $freshMethod->settings);
        $config = $device->fresh()->polling()->unixAgent();
        $this->assertInstanceOf(UnixAgentConfig::class, $config);
        $this->assertEquals(6557, $config->port);

        // 3. Clear the override by submitting empty port
        $response3 = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'unix-agent']),
            [
                'enabled' => '1',
                'affects_availability' => '0',
                'force_save' => '1',
                'settings' => [
                    'port' => '',
                    'timeout' => '',
                ],
            ]
        );
        $response3->assertOk();

        $freshMethod = $method->fresh();
        $this->assertEquals([], $freshMethod->settings);
        $config = $device->fresh()->polling()->unixAgent();
        $this->assertInstanceOf(UnixAgentConfig::class, $config);
        $this->assertEquals(6556, $config->port);
    }

    public function testUpdateIcmpSettingsSavesIpVersion(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create(['hostname' => 'icmp-device.example.com']);
        $method = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Icmp,
            'enabled' => true,
            'affects_availability' => true,
            'settings' => [],
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('device.edit.polling.update', ['device' => $device, 'methodType' => 'icmp']),
            [
                'enabled' => '1',
                'affects_availability' => '1',
                'force_save' => '1',
                'settings' => [
                    'ip_version' => 'ipv6',
                ],
            ]
        );
        $response->assertOk();

        $freshMethod = $method->fresh();
        $this->assertEquals(['ip_version' => 'ipv6'], $freshMethod->settings);
        $config = $device->fresh()->polling()->icmp();
        $this->assertSame('ipv6', $config->ipVersion);
        $icmpMethod = app(PollingMethodRegistry::class)->get(PollingMethodType::Icmp);
        $this->assertInstanceOf(IcmpPollingMethod::class, $icmpMethod);
        $this->assertSame(AddressFamily::IPv6, $icmpMethod->resolveAddressFamily($device->fresh(), $config));
    }

    public function testUpdateStoresOnlyUserSetSettings(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        $method = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'settings' => ['transport' => 'tcp', 'max_repeaters' => 20],
        ]);

        $this->actingAs($admin)->putJson(route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']), [
            'force_save' => '1',
            'settings' => ['transport' => '', 'port' => '161', 'max_repeaters' => '', 'context' => 'vrf-a'],
        ])->assertOk();

        // empty fields (the "Default" option) are not stored, even values matching a default are kept
        $this->assertSame(['port' => 161, 'context' => 'vrf-a'], $method->fresh()->settings);
    }

    public function testUpdateWithoutSecretModeKeepsSecret(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Kept Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'kept'],
        ]);
        $device = Device::factory()->create();
        $method = DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $secret->id,
        ]);

        $this->actingAs($admin)->putJson(route('device.edit.polling.update', ['device' => $device, 'methodType' => 'snmp']), [
            'enabled' => '0',
        ])->assertOk();

        $this->assertSame($secret->id, $method->fresh()->secret_id);
        $this->assertFalse($method->fresh()->enabled);
    }

    public function testSecretPermissionsFollowSecretMode(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo('device.update');
        Permission::findOrCreate('device.viewAll');
        $user->givePermissionTo('device.viewAll'); // may use any secret

        $secret = Secret::create([
            'description' => 'Usable Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'usable'],
        ]);
        $device = Device::factory()->create();
        $store = route('device.edit.polling.store', ['device' => $device]);

        // creating a secret needs secret.create
        $this->actingAs($user)->postJson($store, [
            'method_type' => 'snmp',
            'force_save' => '1',
            'secret_mode' => 'new',
            'description' => 'Not Allowed',
            'secret_data' => ['version' => 'v2c', 'community' => 'nope'],
        ])->assertForbidden();

        // using an existing secret does not
        $this->actingAs($user)->postJson($store, [
            'method_type' => 'snmp',
            'force_save' => '1',
            'secret_mode' => 'existing',
            'secret_id' => $secret->id,
        ])->assertOk();

        $this->assertSame($secret->id, $device->fresh()->pollingMethod(PollingMethodType::Snmp)->secret_id);
        $this->assertDatabaseMissing('secrets', ['description' => 'Not Allowed']);
    }

    public function testTabParameterIsNotInjectedIntoScript(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $device = Device::factory()->create();
        DevicePollingMethod::factory()->create(['device_id' => $device->device_id, 'method_type' => PollingMethodType::Icmp, 'enabled' => true]);

        $response = $this->actingAs($admin)->get(route('device.edit.polling', ['device' => $device, 'tab' => "');alert(1);//"]));

        $response->assertOk();
        $this->assertStringNotContainsString('alert(1)', $response->getContent());
    }

    public function testSecretDataIsLoadedMaskedWithoutUnmask(): void
    {
        $device = Device::factory()->create();
        $secret = Secret::create([
            'description' => 'unmask-test',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'topsecret'],
        ]);
        DevicePollingMethod::factory()->create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'secret_id' => $secret->id,
        ]);
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(['device.update', 'secret.view', 'secret.update']);
        \DB::table('devices_perms')->insert(['user_id' => $user->user_id, 'device_id' => $device->device_id]);

        $this->actingAs($user)->getJson(route('secrets.show', $secret))
            ->assertOk()
            ->assertJsonPath('description', 'unmask-test')
            ->assertJsonPath('usage_count', 1)
            ->assertJsonPath('data.version', 'v2c')
            ->assertJsonPath('data.community', SecretDefinition::MASK);

        $user->givePermissionTo('secret.unmask');
        $this->actingAs($user)->getJson(route('secrets.show', $secret))
            ->assertOk()
            ->assertJsonPath('data.community', 'topsecret');
    }

    public function testSecretDataIsNotLoadedWithoutAccess(): void
    {
        $secret = Secret::create([
            'description' => 'other-device-secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'topsecret'],
        ]);
        DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Snmp,
            'secret_id' => $secret->id,
        ]);
        $user = User::factory()->create(['enabled' => 1]);
        $user->givePermissionTo(['device.update', 'secret.unmask']);

        $this->actingAs($user)->getJson(route('secrets.show', $secret))->assertNotFound();
    }

    public function testSaveResponseDoesNotContainSecretData(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        $device = Device::factory()->create();

        $content = $this->actingAs($admin)->postJson(route('device.edit.polling.store', ['device' => $device]), [
            'method_type' => 'snmp',
            'enabled' => 1,
            'force_save' => 1,
            'secret_mode' => 'new',
            'description' => 'new-secret',
            'secret_data' => ['version' => 'v2c', 'community' => 'topsecret'],
        ])->assertOk()->getContent();

        $this->assertStringContainsString('new-secret', $content);
        $this->assertStringNotContainsString('topsecret', $content);
    }

    public function testDefaultSecretDescriptionOnEditPageIsUnique(): void
    {
        $device = Device::factory()->create(['hostname' => 'probe.example.com']);
        Secret::create([
            'description' => 'SNMP probe.example.com',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'x'],
        ]);
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $content = $this->actingAs($admin)->get(route('device.edit.polling', $device))->assertOk()->getContent();

        $this->assertStringContainsString('SNMP probe.example.com (2)', $content);
    }
}
