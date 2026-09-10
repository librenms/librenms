<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic roles
        Role::findOrCreate('admin');
        Role::findOrCreate('user');

        // Setup permissions
        Permission::findOrCreate('user.viewAny');
        Permission::findOrCreate('user.view');
        Permission::findOrCreate('user.create');
        Permission::findOrCreate('user.update');
        Permission::findOrCreate('user.delete');
        Permission::findOrCreate('user.manage');
        Permission::findOrCreate('role.update');

        Password::defaults(fn () => Password::min(8));

        LibrenmsConfig::set('auth_mechanism', 'mysql');
    }

    public function testAdminWithCreatePermissionCanCreateUser(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('user.create');

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'username' => 'newuser',
            'new_password' => 'password123',
            'new_password_confirmation' => 'password123',
            'realname' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['username' => 'newuser']);
    }

    public function testAdminWithUpdatePermissionCanUpdateAnyUser(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('user.update');

        $targetUser = User::factory()->create(['username' => 'target', 'enabled' => 1]);

        $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
            'realname' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Updated Name', $targetUser->fresh()->realname);
    }

    public function testUserCanUpdateTheirOwnProfile(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old_password'), 'enabled' => 1]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->put(route('users.update', $user), [
            'realname' => 'My New Name',
            'email' => 'me@example.com',
        ]);

        $response->assertRedirect();
        $this->assertEquals('My New Name', $user->fresh()->realname);
    }

    public function testUserCanChangeTheirOwnPasswordWithOldPasswordVerification(): void
    {
        $user = User::factory()->create(['password' => Hash::make('old_password'), 'enabled' => 1]);
        $user->assignRole('user');

        // Without old password - should fail
        $response = $this->actingAs($user)->put(route('users.update', $user), [
            'new_password' => 'new_password123',
            'new_password_confirmation' => 'new_password123',
        ]);
        $response->assertSessionHasErrors('old_password');

        // With correct old password - should succeed
        $response = $this->actingAs($user)->put(route('users.update', $user), [
            'old_password' => 'old_password',
            'new_password' => 'new_password123',
            'new_password_confirmation' => 'new_password123',
        ]);
        $response->assertRedirect();
        $this->assertTrue(Hash::check('new_password123', $user->fresh()->password));
    }

    public function testUserCannotUpdateOtherUsers(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $otherUser = User::factory()->create(['realname' => 'Other User', 'enabled' => 1]);

        $response = $this->actingAs($user)->put(route('users.update', $otherUser), [
            'realname' => 'Hacked Name',
        ]);

        $response->assertForbidden();
        $this->assertEquals('Other User', $otherUser->fresh()->realname);
    }

    public function testUserCannotUpdateRestrictedFieldsOnThemselves(): void
    {
        $user = User::factory()->create([
            'enabled' => 1,
            'can_modify_passwd' => 1,
            'username' => 'testuser',
        ]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->put(route('users.update', $user), [
            'realname' => 'Modified Name',
            'enabled' => 0,
            'can_modify_passwd' => 0,
            'roles' => ['admin'],
        ]);

        $response->assertRedirect();

        $user->refresh();
        $this->assertEquals('Modified Name', $user->realname);
        $this->assertEquals(1, (int) $user->enabled, 'User should NOT be able to change their own enabled status');
        $this->assertEquals(1, (int) $user->can_modify_passwd, 'User should NOT be able to change their own password modification permission');
        $this->assertFalse($user->hasRole('admin'), 'User should NOT be able to assign themselves roles');
    }

    public function testAdminCanUpdateRolesOnOtherUsers(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('user.update');
        $admin->givePermissionTo('role.update');

        $targetUser = User::factory()->create(['username' => 'target']);
        $this->assertFalse($targetUser->hasRole('admin'));

        $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
            'roles' => ['admin'],
        ]);

        $response->assertRedirect();
        $this->assertTrue($targetUser->fresh()->hasRole('admin'));
    }

    public function testAdminCanUpdateRestrictedFieldsOnOtherUsers(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('user.update');

        $targetUser = User::factory()->create([
            'enabled' => 1,
            'can_modify_passwd' => 1,
            'username' => 'target',
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
            'enabled' => 0,
            'can_modify_passwd' => 0,
        ]);

        $response->assertRedirect();

        $targetUser->refresh();
        $this->assertEquals(0, (int) $targetUser->enabled);
        $this->assertEquals(0, (int) $targetUser->can_modify_passwd);
    }

    public function testAdminCanUncheckRestrictedFields(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $admin->givePermissionTo('user.update');

        $targetUser = User::factory()->create([
            'enabled' => 1,
            'can_modify_passwd' => 1,
            'username' => 'target',
        ]);

        // Sending without enabled/can_modify_passwd should uncheck them (as they are checkboxes)
        // when an admin is updating another user.
        $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
            'realname' => 'Name Changed',
        ]);

        $response->assertRedirect();
        $targetUser->refresh();
        $this->assertEquals(0, (int) $targetUser->enabled);
        $this->assertEquals(0, (int) $targetUser->can_modify_passwd);

        // To explicitly set them to false.
        $response = $this->actingAs($admin)->put(route('users.update', $targetUser), [
            'enabled' => 0,
            'can_modify_passwd' => 0,
        ]);

        $response->assertRedirect();
        $targetUser->refresh();
        $this->assertEquals(0, (int) $targetUser->enabled);
        $this->assertEquals(0, (int) $targetUser->can_modify_passwd);
    }

    public function testAdminWithManagePermissionCanViewUserPermissionsPage(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $targetUser = User::factory()->create(['enabled' => 1]);

        $admin->givePermissionTo('user.manage');

        $response = $this->actingAs($admin)->get(route('users.permissions.edit', $targetUser));

        $response->assertOk();
        $response->assertSee('Device Access');
    }

    public function testAdminWithManagePermissionCanGrantAndRevokeDevicePermission(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $targetUser = User::factory()->create(['enabled' => 1]);
        $device = Device::factory()->create();

        $admin->givePermissionTo('user.manage');

        $addResponse = $this->actingAs($admin)->post(route('users.permissions.device.attach', $targetUser), [
            'device_id' => $device->device_id,
        ]);

        $addResponse->assertRedirect(route('users.permissions.edit', [$targetUser, 'tab' => 'device']));
        $this->assertDatabaseHas('devices_perms', [
            'user_id' => $targetUser->user_id,
            'device_id' => $device->device_id,
        ]);

        $perm = DevicePerm::query()->where('user_id', $targetUser->user_id)->where('device_id', $device->device_id)->firstOrFail();

        $removeResponse = $this->actingAs($admin)->delete(route('users.permissions.device.detach', [$targetUser, $perm->device_id]));

        $removeResponse->assertRedirect(route('users.permissions.edit', [$targetUser, 'tab' => 'device']));
        $this->assertDatabaseMissing('devices_perms', [
            'user_id' => $targetUser->user_id,
            'device_id' => $device->device_id,
        ]);
    }
}
