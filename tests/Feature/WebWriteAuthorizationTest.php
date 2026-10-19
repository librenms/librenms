<?php

/**
 * WebWriteAuthorizationTest.php
 *
 * Verifies that web write operations are scoped to the devices and ports the
 * user can access, not just the global permission.
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
use App\Models\Port;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class WebWriteAuthorizationTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $accessible;
    private Device $other;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('user');
        $this->accessible = Device::factory()->create();
        $this->other = Device::factory()->create();
    }

    protected function tearDown(): void
    {
        $_POST = [];

        parent::tearDown();
    }

    /**
     * @param  string[]  $permissions
     */
    private function limitedUser(array $permissions): User
    {
        /** @var User $user */
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        $user->givePermissionTo($permissions);
        $user->devicesOwned()->attach($this->accessible->device_id);

        return $user;
    }

    /**
     * Run a legacy ajax form with the given POST data and return its output.
     *
     * @param  array<string, mixed>  $post
     */
    private function runForm(string $form, array $post): string
    {
        $_POST = $post;
        ob_start();
        try {
            include base_path("includes/html/forms/$form.inc.php");
        } finally {
            $output = (string) ob_get_clean();
        }

        return $output;
    }

    public function testDependencyUpdateIsScopedToAccessibleDevices(): void
    {
        $user = $this->limitedUser(['device.update']);
        $parent = Device::factory()->create();
        $user->devicesOwned()->attach($parent->device_id);

        // child not accessible
        $this->actingAs($user)
            ->putJson(route('device-dependencies.update'), ['device_ids' => [$this->other->device_id], 'parent_ids' => [$parent->device_id]])
            ->assertForbidden();
        $this->assertCount(0, $this->other->parents()->get());

        // parent not accessible
        $this->actingAs($user)
            ->putJson(route('device-dependencies.update'), ['device_ids' => [$this->accessible->device_id], 'parent_ids' => [$this->other->device_id]])
            ->assertForbidden();
        $this->assertCount(0, $this->accessible->parents()->get());

        // both accessible
        $this->actingAs($user)
            ->putJson(route('device-dependencies.update'), ['device_ids' => [$this->accessible->device_id], 'parent_ids' => [$parent->device_id]])
            ->assertOk();
        $this->assertSame([$parent->device_id], $this->accessible->parents()->pluck('device_id')->all());
    }

    public function testDependencyDestroyIsScopedToAccessibleDevices(): void
    {
        $user = $this->limitedUser(['device.update']);
        $this->other->parents()->attach($this->accessible->device_id);

        // parent accessible, but its child is not
        $this->actingAs($user)
            ->deleteJson(route('device-dependencies.destroy'), ['parent_ids' => [$this->accessible->device_id]])
            ->assertForbidden();
        $this->assertCount(1, $this->other->parents()->get());

        $child = Device::factory()->create();
        $user->devicesOwned()->attach($child->device_id);
        $child->parents()->attach($this->other->device_id);

        // parent not accessible
        $this->actingAs($user)
            ->deleteJson(route('device-dependencies.destroy'), ['parent_ids' => [$this->other->device_id]])
            ->assertForbidden();
        $this->assertCount(1, $child->parents()->get());
    }

    public function testPortNotesRequireAccessToThePort(): void
    {
        $user = $this->limitedUser(['port.update']);
        $this->actingAs($user);
        $port = Port::factory()->create(['device_id' => $this->accessible->device_id]);
        $otherPort = Port::factory()->create(['device_id' => $this->other->device_id]);

        $output = $this->runForm('update-port-notes', [
            'device_id' => $this->accessible->device_id,
            'port_id_notes' => 'port_id_notes:' . $port->port_id,
            'notes' => 'my notes',
        ]);
        $this->assertSame('ok', json_decode($output, true)['status'] ?? null);
        $this->assertSame('my notes', $this->accessible->getAttrib('port_id_notes:' . $port->port_id));

        try {
            $this->runForm('update-port-notes', [
                'device_id' => $this->other->device_id,
                'port_id_notes' => 'port_id_notes:' . $otherPort->port_id,
                'notes' => 'bad',
            ]);
            $this->fail('Updating notes on an inaccessible port should be denied');
        } catch (AuthorizationException) {
            $this->assertNull($this->other->getAttrib('port_id_notes:' . $otherPort->port_id));
        }
    }

    public function testPortNotesRejectMismatchedDeviceAndAttribute(): void
    {
        $user = $this->limitedUser(['port.update']);
        $this->actingAs($user);
        $port = Port::factory()->create(['device_id' => $this->accessible->device_id]);

        $invalid = [
            ['device_id' => $this->other->device_id, 'port_id_notes' => 'port_id_notes:' . $port->port_id],
            ['device_id' => $this->accessible->device_id, 'port_id_notes' => 'override_icmp_disable'],
        ];

        foreach ($invalid as $post) {
            try {
                $this->runForm('update-port-notes', $post + ['notes' => 'bad']);
                $this->fail('Invalid port notes request should be rejected');
            } catch (HttpException $e) {
                $this->assertSame(400, $e->getStatusCode());
            }
        }

        $this->assertNull($this->other->getAttrib('port_id_notes:' . $port->port_id));
        $this->assertNull($this->accessible->getAttrib('override_icmp_disable'));
    }

    public function testApplicationUpdateIsScopedToAccessibleDevices(): void
    {
        $user = $this->limitedUser(['application.update']);
        $this->actingAs($user);

        $output = $this->runForm('application-update', [
            'device_id' => $this->other->device_id,
            'application' => 'nginx',
            'state' => 'true',
        ]);
        $this->assertSame('You need permission', json_decode($output, true)['message'] ?? null);
        $this->assertFalse($this->other->applications()->withTrashed()->exists());

        $output = $this->runForm('application-update', [
            'device_id' => $this->accessible->device_id,
            'application' => 'nginx',
            'state' => 'true',
        ]);
        $this->assertSame('Application enabled', json_decode($output, true)['message'] ?? null);
        $this->assertTrue($this->accessible->applications()->where('app_type', 'nginx')->exists());
    }
}
