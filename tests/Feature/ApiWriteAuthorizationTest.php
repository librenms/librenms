<?php

/**
 * ApiWriteAuthorizationTest.php
 *
 * Verifies that write operations on devices, components and bills are scoped
 * to the resources the user can access, not just the global permission.
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

use App\Models\Bill;
use App\Models\Component;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class ApiWriteAuthorizationTest extends DBTestCase
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
     * @return array{X-Auth-Token: string}
     */
    private function headers(User $user): array
    {
        return ['X-Auth-Token' => $user->createToken('test')->plainTextToken];
    }

    public function testDevicePolicyUpdateAndDeleteAreScopedToAccessibleDevices(): void
    {
        $user = $this->limitedUser(['device.update', 'device.delete']);

        $this->assertTrue(Gate::forUser($user)->allows('update', Device::class));
        $this->assertTrue(Gate::forUser($user)->allows('update', $this->accessible));
        $this->assertFalse(Gate::forUser($user)->allows('update', $this->other));
        $this->assertTrue(Gate::forUser($user)->allows('delete', $this->accessible));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $this->other));
    }

    public function testApiDeviceWritesDenyInaccessibleDevice(): void
    {
        $user = $this->limitedUser(['device.update', 'device.delete']);
        $headers = $this->headers($user);
        $other = $this->other->hostname;

        $this->json('PATCH', "/api/v0/devices/$other", ['field' => 'purpose', 'data' => 'changed'], $headers)->assertStatus(403);
        $this->json('PATCH', "/api/v0/devices/$other/rename/renamed.example.com", [], $headers)->assertStatus(403);
        $this->json('POST', "/api/v0/devices/$other/maintenance", ['duration' => '1:00'], $headers)->assertStatus(403);
        $this->json('POST', "/api/v0/devices/{$this->other->device_id}/parents", ['parent_ids' => (string) $this->accessible->device_id], $headers)->assertStatus(403);
        $this->json('DELETE', "/api/v0/devices/{$this->other->device_id}/parents", [], $headers)->assertStatus(403);
        $this->json('DELETE', "/api/v0/devices/$other", [], $headers)->assertStatus(403);

        $this->other->refresh();
        $this->assertNotEquals('changed', $this->other->purpose);
        $this->assertSame($other, $this->other->hostname);
        $this->assertTrue($this->other->parents()->doesntExist());

        $this->json('PATCH', "/api/v0/devices/{$this->accessible->hostname}", ['field' => 'purpose', 'data' => 'changed'], $headers)->assertStatus(200);
        $this->assertSame('changed', $this->accessible->refresh()->purpose);
    }

    public function testComponentWritesAreScopedToAccessibleDevices(): void
    {
        $user = $this->limitedUser(['component.update', 'component.delete']);
        $headers = $this->headers($user);
        $own = Component::factory()->create(['device_id' => $this->accessible->device_id, 'label' => 'original']);
        $foreign = Component::factory()->create(['device_id' => $this->other->device_id, 'label' => 'original']);

        // foreign component addressed through an accessible device is ignored
        $this->json('PUT', "/api/v0/devices/{$this->accessible->hostname}/components", [$foreign->id => ['label' => 'tampered']], $headers)
            ->assertStatus(200);
        $this->assertSame('original', $foreign->refresh()->label);

        // foreign component addressed through its own device is denied
        $this->json('PUT', "/api/v0/devices/{$this->other->hostname}/components", [$foreign->id => ['label' => 'tampered']], $headers)
            ->assertStatus(403);
        $this->assertSame('original', $foreign->refresh()->label);

        // device cannot be changed
        $this->json('PUT', "/api/v0/devices/{$this->accessible->hostname}/components", [$own->id => ['label' => 'changed', 'device_id' => $this->other->device_id]], $headers)
            ->assertStatus(200);
        $own->refresh();
        $this->assertSame('changed', $own->label);
        $this->assertSame($this->accessible->device_id, $own->device_id);

        $this->json('DELETE', "/api/v0/devices/{$this->accessible->hostname}/components/$foreign->id", [], $headers)->assertStatus(404);
        $this->json('DELETE', "/api/v0/devices/{$this->other->hostname}/components/$foreign->id", [], $headers)->assertStatus(403);
        $this->assertTrue(Component::whereKey($foreign->id)->exists());

        $this->json('DELETE', "/api/v0/devices/{$this->accessible->hostname}/components/$own->id", [], $headers)->assertStatus(200);
        $this->assertFalse(Component::whereKey($own->id)->exists());
    }

    public function testBillWritesAreScopedToAccessibleBills(): void
    {
        $user = $this->limitedUser(['bill.create', 'bill.update', 'bill.delete']);
        $headers = $this->headers($user);
        $own = Bill::factory()->create(['bill_notes' => 'original']);
        $foreign = Bill::factory()->create(['bill_notes' => 'original']);
        $user->bills()->attach($own->bill_id);

        $this->json('POST', '/api/v0/bills', ['bill_id' => $foreign->bill_id, 'bill_notes' => 'tampered'], $headers)->assertStatus(403);
        $this->assertSame('original', $foreign->refresh()->bill_notes);

        $this->json('DELETE', "/api/v0/bills/$foreign->bill_id", [], $headers)->assertStatus(403);
        $this->assertTrue(Bill::whereKey($foreign->bill_id)->exists());

        $this->json('POST', '/api/v0/bills', ['bill_id' => $own->bill_id, 'bill_notes' => 'changed'], $headers)->assertStatus(200);
        $this->assertSame('changed', $own->refresh()->bill_notes);

        $this->json('DELETE', "/api/v0/bills/$own->bill_id", [], $headers)->assertStatus(200);
        $this->assertFalse(Bill::whereKey($own->bill_id)->exists());
    }
}
