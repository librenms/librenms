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
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
        ]);

        $this->actingAs($admin)->get(route('secrets.index'))
            ->assertOk()
            ->assertSee('1 secret configured')
            ->assertSee(route('settings', ['tab' => 'poller', 'section' => 'snmp']))
            ->assertSee("confirm('Are you sure you want to delete this secret?')", false);

        Secret::create([
            'description' => 'Second Secret',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'private'],
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
            'secret_type' => 'snmp-community',
            'community' => 'public',
        ]);

        $response->assertRedirect(route('secrets.index'));
        $this->assertDatabaseHas('secrets', [
            'description' => 'Unique SNMP Secret',
        ]);
    }

    public function testStoreSecretWithoutDataSucceeds(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        // every IPMI field is optional
        $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Empty IPMI Secret',
            'secret_type' => 'ipmi',
        ])->assertRedirect(route('secrets.index'));

        $secret = Secret::where('description', 'Empty IPMI Secret')->firstOrFail();
        $this->assertSame([], $secret->data);
    }

    public function testShowRequiresSecretPermission(): void
    {
        $secret = Secret::create([
            'description' => 'Default Shown',
            'secret_type' => SecretType::SnmpV3,
            'data' => ['authname' => 'security-name'],
        ]);
        LibrenmsConfig::set('snmp.default_credentials', [$secret->id]);

        // default credentials are in hasAccess for every user, the permission check must still apply
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole(Role::findOrCreate('user'));
        $this->actingAs($user)->getJson(route('secrets.show', $secret))->assertForbidden();

        $user->givePermissionTo('secret.view');
        $this->actingAs($user)->getJson(route('secrets.show', $secret))
            ->assertOk()
            ->assertJsonPath('description', 'Default Shown');
    }

    public function testStoreSecretFailsWithDuplicateDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        Secret::create([
            'description' => 'Existing Secret',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Existing Secret',
            'secret_type' => 'snmp-community',
            'community' => 'public',
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function testUpdateSecretAllowsSameDescription(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Existing Secret',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->put(route('secrets.update', $secret), [
            'description' => 'Existing Secret',
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
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
        ]);

        $secret2 = Secret::create([
            'description' => 'Second Secret',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
        ]);

        $response = $this->actingAs($admin)->put(route('secrets.update', $secret2), [
            'description' => 'First Secret',
            'community' => 'public',
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function testCreateSecretRendersFormWithDefaults(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $response = $this->actingAs($admin)->get(route('secrets.create', ['type' => 'snmp-v3']));

        $response->assertOk();
        $response->assertViewIs('secrets.create');
        $response->assertViewHas('data', fn (array $data): bool => ($data['authlevel'] ?? null) === 'noAuthNoPriv');
        $response->assertSee('x-data="{ formData:', false);
        $response->assertSee('x-model="formData[\'authlevel\']"', false);
        $response->assertDontSee('x-model="formData[\'version\']"', false); // the version is a polling method setting
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
            'secret_type' => SecretType::SnmpV3,
            'data' => [
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
        $response->assertViewHas('data', fn (array $data): bool => ($data['authname'] ?? null) === 'myuser');
        $response->assertSee('x-data="{ formData:', false);
        $response->assertSee('x-model="formData[\'authname\']"', false);
    }

    public function testDestroySecretSucceedsWhenNotInUse(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $secret = Secret::create([
            'description' => 'Unused Secret',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
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
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'keep'],
        ]);
        $remove = Secret::create([
            'description' => 'Default Remove',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'remove'],
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
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
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

    public function testSecretOfUnknownTypeIsShownGenericallyAndKeepsItsData(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);
        Permission::findOrCreate('secret.unmask');
        $secret = Secret::create([
            'description' => 'Future Secret',
            'secret_type' => 'future-secret',
            'data' => ['token' => 'abc123', 'port' => 8443],
        ]);

        $this->actingAs($admin)->get(route('secrets.index'))
            ->assertOk()
            ->assertSee('Unknown (future-secret)');

        $this->actingAs($admin)->get(route('secrets.edit', $secret))
            ->assertOk()
            ->assertSee('Unknown (future-secret)')
            ->assertSee('token')
            ->assertSee('8443');

        $this->actingAs($admin)->getJson(route('secrets.show', $secret))
            ->assertOk()
            ->assertJsonPath('secret_type', 'future-secret');

        $this->actingAs($admin)->put(route('secrets.update', $secret), [
            'description' => 'Renamed Future Secret',
            'token' => 'changed',
        ])->assertRedirect(route('secrets.index'));

        $secret = $secret->fresh();
        $this->assertSame('Renamed Future Secret', $secret->description);
        $this->assertSame('future-secret', $secret->typeName());
        $this->assertSame(['token' => 'abc123', 'port' => 8443], $secret->data);
    }

    public function testSecretOfUnknownTypeCanNotBeCreated(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $this->actingAs($admin)->post(route('secrets.store'), [
            'description' => 'Unknown Secret',
            'secret_type' => 'unknown',
        ])->assertStatus(400);

        $this->assertDatabaseMissing('secrets', ['description' => 'Unknown Secret']);
    }

    public function testIndexShowsDisabledDeleteButtonWhenSecretInUse(): void
    {
        $admin = User::factory()->admin()->create(['enabled' => 1]);

        $device = Device::factory()->create();
        $secret = Secret::create([
            'description' => 'In Use Secret',
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'public'],
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
