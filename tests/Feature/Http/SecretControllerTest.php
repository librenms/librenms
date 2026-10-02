<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class SecretControllerTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Permission::findOrCreate('secret.create');
        Permission::findOrCreate('secret.update');
        Permission::findOrCreate('secret.view');
        Permission::findOrCreate('secret.delete');
    }

    public function testIndexListsSecretsWithPluralizedCount(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        Secret::query()->delete(); // migrations seed default secrets

        Secret::create([
            'description' => 'First Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $this->actingAs($admin)->get(route('secrets.index'))
            ->assertOk()
            ->assertSee('1 secret configured')
            ->assertSee(route('settings', ['tab' => 'poller', 'section' => 'snmp']))
            ->assertSee("confirm('Are you sure you want to delete this secret?')", false);

        Secret::create([
            'description' => 'Second Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'private'],
        ]);

        $this->actingAs($admin)->get(route('secrets.index'))
            ->assertOk()
            ->assertSee('2 secrets configured');
    }

    public function testStoreSecretSucceedsWithUniqueDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $response = $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Unique SNMP Secret',
            'secret_type' => 'snmp',
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
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        Secret::create([
            'description' => 'Existing Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Existing Secret',
            'secret_type' => 'snmp',
            'version' => 'v2c',
            'community' => 'public',
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function testUpdateSecretAllowsSameDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Existing Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->put(route('secrets.update', $secret), [
            'description' => 'Existing Secret',
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
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        Secret::create([
            'description' => 'First Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $secret2 = Secret::create([
            'description' => 'Second Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->put(route('secrets.update', $secret2), [
            'description' => 'First Secret',
            'version' => 'v2c',
            'community' => 'public',
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function testCreateSecretRendersFormWithDefaults(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $response = $this->actingAs($admin)->get(route('secrets.create', ['type' => 'snmp']));

        $response->assertOk();
        $response->assertViewIs('secrets.create');
        $response->assertViewHas('data', fn (array $data): bool => ($data['version'] ?? null) === 'v2c');
        $response->assertSee('x-data="{ formData:', false);
        $response->assertSee('x-model="formData[\'version\']"', false);
        $response->assertSee('Add Secret');
        $response->assertSee('Back to Secrets');
        $response->assertSee('Select Secret Type:');
        $response->assertDontSee('Credential');
    }

    public function testEditSecretRendersFormWithSecretData(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Test SNMP v3 Secret',
            'secret_type' => SecretType::Snmp,
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
        $response->assertViewHas('data', fn (array $data): bool => ($data['version'] ?? null) === 'v3' && ($data['authname'] ?? null) === 'myuser');
        $response->assertSee('x-data="{ formData:', false);
        $response->assertSee('x-model="formData[\'version\']"', false);
    }

    public function testDestroySecretSucceedsWhenNotInUse(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Unused Secret',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->delete(route('secrets.destroy', $secret));

        $response->assertRedirect(route('secrets.index'));
        $this->assertDatabaseMissing('secrets', [
            'id' => $secret->id,
        ]);
    }

    public function testDestroySecretRemovesItFromDefaultCredentials(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $keep = Secret::create([
            'description' => 'Default Keep',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'keep'],
        ]);
        $remove = Secret::create([
            'description' => 'Default Remove',
            'secret_type' => SecretType::Snmp,
            'data' => ['version' => 'v2c', 'community' => 'remove'],
        ]);
        LibrenmsConfig::persist('snmp.default_credentials', [$remove->id, $keep->id]);

        $this->actingAs($admin)->delete(route('secrets.destroy', $remove))
            ->assertRedirect(route('secrets.index'));

        $this->assertSame([$keep->id], LibrenmsConfig::get('snmp.default_credentials'));
        $this->assertDatabaseHas('config', [
            'config_name' => 'snmp.default_credentials',
            'config_value' => json_encode([$keep->id]),
        ]);
    }

    public function testDestroySecretFailsWhenInUse(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        $secret = Secret::create([
            'description' => 'In Use Secret',
            'secret_type' => SecretType::Snmp,
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
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        $secret = Secret::create([
            'description' => 'In Use Secret',
            'secret_type' => SecretType::Snmp,
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
        $response->assertSee('Edit Default Secrets');
        $response->assertSee(url('/settings/poller/snmp'));
    }
}
