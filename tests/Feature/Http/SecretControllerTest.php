<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Secret;
use App\Models\User;
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
}
