<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Storage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditStorageControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    public function testAdminCanViewStoragePage(): void
    {
        $device = Device::factory()->create();
        Storage::factory()->for($device)->create([
            'storage_descr' => '/data <b>disk</b>',
            'storage_size' => 1073741824,
            'storage_perc' => 42,
            'storage_perc_warn' => 75,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.storage', $device))
            ->assertOk()
            ->assertSee('/data &lt;b&gt;disk&lt;/b&gt;', false)
            ->assertDontSee('/data <b>disk</b>', false)
            ->assertSee('1 GiB')
            ->assertSee('42%')
            ->assertSee('value="75"', false);
    }

    public function testNullWarnThresholdRendersEmpty(): void
    {
        $device = Device::factory()->create();
        $storage = Storage::factory()->for($device)->create();
        Storage::whereKey($storage->storage_id)->update(['storage_perc_warn' => null]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.storage', $device))
            ->assertOk()
            ->assertSee('value=""', false);
    }

    public function testUserCannotViewStoragePage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.storage', $device))
            ->assertForbidden();
    }

    public function testAdminCanUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $storage = Storage::factory()->for($device)->create(['storage_perc_warn' => 60]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.storage.update', [$device, $storage]), ['storage_perc_warn' => '90'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame(90, $storage->fresh()->storage_perc_warn);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidThresholds(): array
    {
        return [
            'empty' => [''],
            'non-numeric' => ['abc'],
            'negative' => [-1],
            'over 100' => [101],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidThresholds')]
    public function testInvalidWarnThresholdIsRejected(mixed $value): void
    {
        $device = Device::factory()->create();
        $storage = Storage::factory()->for($device)->create(['storage_perc_warn' => 60]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.storage.update', [$device, $storage]), ['storage_perc_warn' => $value])
            ->assertUnprocessable();

        $this->assertSame(60, $storage->fresh()->storage_perc_warn);
    }

    public function testStorageMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherStorage = Storage::factory()->create(['storage_perc_warn' => 60]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.storage.update', [$device, $otherStorage]), ['storage_perc_warn' => 90])
            ->assertNotFound();

        $this->assertSame(60, $otherStorage->fresh()->storage_perc_warn);
    }

    public function testUserCannotUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $storage = Storage::factory()->for($device)->create(['storage_perc_warn' => 60]);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.storage.update', [$device, $storage]), ['storage_perc_warn' => 90])
            ->assertForbidden();

        $this->assertSame(60, $storage->fresh()->storage_perc_warn);
    }
}
