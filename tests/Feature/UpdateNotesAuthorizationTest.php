<?php

/**
 * UpdateNotesAuthorizationTest.php
 *
 * Verifies the updateNotes policy ability across roles, in particular that a global-read user
 * only gains the ability once granted the explicit device.updateNotes permission (issue #20127).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class UpdateNotesAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Device $accessible;
    private Device $other;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('global-read');
        Role::findOrCreate('user');
        Permission::findOrCreate('device.updateNotes');
        Permission::findOrCreate('device.view');

        $this->accessible = Device::factory()->create();
        $this->other = Device::factory()->create();
    }

    private function can(User $user, Device $device): bool
    {
        return Gate::forUser($user)->allows('updateNotes', $device);
    }

    public function testGlobalReadNeedsExplicitUpdateNotesPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('global-read');

        $this->assertFalse($this->can($user, $this->accessible));
        $this->assertFalse($this->can($user, $this->other));
    }

    public function testGlobalReadWithExplicitUpdateNotesPermissionCanUpdateNotesOnAnyDevice(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('global-read');
        $user->givePermissionTo('device.updateNotes');

        // global-read's view access covers any device, including ones the user does not own
        $this->assertTrue($this->can($user, $this->accessible));
        $this->assertTrue($this->can($user, $this->other));
    }

    public function testUserIsRestrictedToAccessibleDevices(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->givePermissionTo('device.updateNotes');
        $user->devicesOwned()->attach($this->accessible->device_id);

        // device the user can access
        $this->assertTrue($this->can($user, $this->accessible));

        // device the user cannot access — must be denied
        $this->assertFalse($this->can($user, $this->other));
    }

    public function testUserWithoutUpdateNotesPermissionCannotUpdateNotes(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        $user->devicesOwned()->attach($this->accessible->device_id);

        $this->assertFalse($this->can($user, $this->accessible));
    }
}
