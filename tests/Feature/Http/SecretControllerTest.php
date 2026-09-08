<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use App\Models\User;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SecretControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('secret.create');
        Permission::findOrCreate('secret.update');
        Permission::findOrCreate('secret.view');
        Permission::findOrCreate('secret.delete');
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    public function testStoreSecretSucceedsWithUniqueDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Unique SNMP Secret',
            'secret_type' => 'snmp',
            'default' => '0',
            'version' => 'v2c',
            'community' => 'public',
        ]);

        $response->assertRedirect(route('secrets.index'));
        $this->assertDatabaseHas('secrets', [
            'description' => 'Unique SNMP Secret',
        ]);
    }

    public function testStoreSecretFailsWithDuplicateDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        Secret::create([
            'description' => 'Existing Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Existing Secret',
            'secret_type' => 'snmp',
            'default' => '0',
            'version' => 'v2c',
            'community' => 'public',
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function testUpdateSecretAllowsSameDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $secret = Secret::create([
            'description' => 'Existing Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->put(route('secrets.update', $secret), [
            'description' => 'Existing Secret',
            'default' => '0',
            'version' => 'v2c',
            'community' => 'private',
        ]);

        $response->assertRedirect(route('secrets.index'));
        $this->assertDatabaseHas('secrets', [
            'id' => $secret->id,
            'description' => 'Existing Secret',
        ]);
    }

    public function testUpdateSecretFailsWithDuplicateDescription(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        Secret::create([
            'description' => 'First Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $secret2 = Secret::create([
            'description' => 'Second Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->put(route('secrets.update', $secret2), [
            'description' => 'First Secret',
            'default' => '0',
            'version' => 'v2c',
            'community' => 'public',
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function testCreateSecretRendersFormWithDefaults(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get(route('secrets.create', ['type' => 'snmp']));

        $response->assertOk();
        $response->assertViewIs('secrets.create');
        $response->assertViewHas('data', function (array $data): bool {
            return ($data['version'] ?? null) === 'v2c';
        });
        $response->assertSee('x-data="{ formData:', false);
        $response->assertSee('x-model="formData[\'version\']"', false);
    }

    public function testEditSecretRendersFormWithSecretData(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $secret = Secret::create([
            'description' => 'Test SNMP v3 Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => [
                'version' => 'v3',
                'authname' => 'myuser',
                'authlevel' => 'authPriv',
                'authpass' => 'authpassword',
                'authalgo' => 'SHA-256',
                'cryptopass' => 'cryptopassword',
                'cryptoalgo' => 'AES-256',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('secrets.edit', $secret));

        $response->assertOk();
        $response->assertViewIs('secrets.edit');
        $response->assertViewHas('data', function (array $data): bool {
            return ($data['version'] ?? null) === 'v3' && ($data['authname'] ?? null) === 'myuser';
        });
        $response->assertSee('x-data="{ formData:', false);
        $response->assertSee('x-model="formData[\'version\']"', false);
    }

    public function testDestroySecretSucceedsWhenNotInUse(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $secret = Secret::create([
            'description' => 'Unused Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->delete(route('secrets.destroy', $secret));

        $response->assertRedirect(route('secrets.index'));
        $this->assertDatabaseMissing('secrets', [
            'id' => $secret->id,
        ]);
    }

    public function testDestroySecretFailsWhenInUse(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $device = Device::factory()->create();
        $secret = Secret::create([
            'description' => 'In Use Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        DevicePollingMethod::create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
            'secret_id' => $secret->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('secrets.destroy', $secret));

        $response->assertRedirect(route('secrets.index'));
        $this->assertDatabaseHas('secrets', [
            'id' => $secret->id,
        ]);
    }

    public function testIndexShowsDisabledDeleteButtonWhenSecretInUse(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $device = Device::factory()->create();
        $secret = Secret::create([
            'description' => 'In Use Secret',
            'secret_type' => SecretType::Snmp,
            'default' => false,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        DevicePollingMethod::create([
            'device_id' => $device->device_id,
            'method_type' => PollingMethodType::Snmp,
            'enabled' => true,
            'affects_availability' => true,
            'secret_id' => $secret->id,
        ]);

        $response = $this->actingAs($admin)->get(route('secrets.index'));

        $response->assertOk();
        $response->assertSee('Cannot delete secret in use');
    }
}
