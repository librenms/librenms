<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditWirelessSensorsControllerTest extends TestCase
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function sensor(Device $device, array $attributes = []): WirelessSensor
    {
        return WirelessSensor::factory()->for($device)->create(array_merge([
            'sensor_class' => 'clients',
            'sensor_descr' => 'Radio <b>1</b>',
            'sensor_current' => 12,
            'sensor_limit' => 50,
            'sensor_limit_warn' => 40,
            'sensor_limit_low_warn' => 5,
            'sensor_limit_low' => 1,
            'sensor_alert' => 1,
            'sensor_custom' => 'No',
        ], $attributes));
    }

    public function testAdminCanViewWirelessSensorsPage(): void
    {
        $device = Device::factory()->create();
        $this->sensor($device);
        $this->sensor($device, ['sensor_descr' => 'Deleted sensor', 'sensor_deleted' => 1]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.wireless-sensors', $device))
            ->assertOk()
            ->assertSee('Radio &lt;b&gt;1&lt;/b&gt;', false)
            ->assertDontSee('Radio <b>1</b>', false)
            ->assertDontSee('Deleted sensor')
            ->assertSee('value="50"', false)
            ->assertSee('value="40"', false)
            ->assertSee('value="5"', false)
            ->assertSee('value="1"', false);
    }

    public function testUserCannotViewWirelessSensorsPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.wireless-sensors', $device))
            ->assertForbidden();
    }

    public function testAdminCanUpdateLimit(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_limit_warn' => '45.5'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $sensor->refresh();
        $this->assertEquals(45.5, $sensor->sensor_limit_warn);
        $this->assertSame('Yes', $sensor->sensor_custom);
    }

    public function testEmptyLimitClearsIt(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_limit_low' => ''])
            ->assertOk();

        $this->assertNull($sensor->fresh()->sensor_limit_low);
    }

    public function testInvalidLimitIsRejected(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_limit' => 'abc'])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_current' => '1'])
            ->assertUnprocessable();

        $sensor->refresh();
        $this->assertEquals(50, $sensor->sensor_limit);
        $this->assertEquals(12, $sensor->sensor_current);
        $this->assertSame('No', $sensor->sensor_custom);
    }

    public function testEmptyUpdateIsRejected(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), [])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_custom' => 'Yes'])
            ->assertUnprocessable();

        $this->assertSame('No', $sensor->fresh()->sensor_custom);
    }

    public function testSensorMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherSensor = $this->sensor(Device::factory()->create());

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $otherSensor]), ['sensor_limit' => 99])
            ->assertNotFound();

        $this->assertEquals(50, $otherSensor->fresh()->sensor_limit);
    }

    public function testUserCannotUpdateLimit(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_limit' => 99])
            ->assertForbidden();

        $this->assertEquals(50, $sensor->fresh()->sensor_limit);
    }

    public function testAdminCanToggleAlerts(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_alert' => 0])
            ->assertOk()
            ->assertJson(['status' => 'ok']);
        $this->assertEquals(0, $sensor->fresh()->sensor_alert);
        $this->assertSame('No', $sensor->fresh()->sensor_custom);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_alert' => 1])
            ->assertOk()
            ->assertJson(['status' => 'ok']);
        $this->assertEquals(1, $sensor->fresh()->sensor_alert);
    }

    public function testUserCannotToggleAlerts(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_alert' => 0])
            ->assertForbidden();

        $this->assertEquals(1, $sensor->fresh()->sensor_alert);
    }

    public function testAdminCanRemoveCustomLimit(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device, ['sensor_custom' => 'Yes']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_custom' => 'No'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $sensor->refresh();
        $this->assertSame('No', $sensor->sensor_custom);
        $this->assertLimitsCleared($sensor);
    }

    public function testDiscoverySetsLimitsAfterReset(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_limit' => 99])
            ->assertOk();
        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.update', [$device, $sensor]), ['sensor_custom' => 'No'])
            ->assertOk();

        // discovery updates through WirelessSensorObserver
        $discovered = $sensor->fresh();
        $discovered->fill(['sensor_limit' => 70, 'sensor_limit_warn' => 60, 'sensor_limit_low_warn' => 3, 'sensor_limit_low' => 2]);
        $discovered->save();

        $discovered->refresh();
        $this->assertEquals(70, $discovered->sensor_limit);
        $this->assertEquals(60, $discovered->sensor_limit_warn);
        $this->assertEquals(3, $discovered->sensor_limit_low_warn);
        $this->assertEquals(2, $discovered->sensor_limit_low);
    }

    public function testAdminCanResetAllCustomLimits(): void
    {
        $device = Device::factory()->create();
        $first = $this->sensor($device, ['sensor_custom' => 'Yes']);
        $second = $this->sensor($device, ['sensor_custom' => 'Yes']);
        $other = $this->sensor(Device::factory()->create(), ['sensor_custom' => 'Yes']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.reset', $device))
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        foreach ([$first->fresh(), $second->fresh()] as $sensor) {
            $this->assertSame('No', $sensor->sensor_custom);
            $this->assertLimitsCleared($sensor);
        }

        $other->refresh();
        $this->assertSame('Yes', $other->sensor_custom);
        $this->assertEquals(50, $other->sensor_limit);
    }

    public function testResetAllLeavesDiscoveredLimitsAlone(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device, ['sensor_custom' => 'No']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.wireless-sensors.reset', $device))
            ->assertOk();

        $this->assertEquals(50, $sensor->fresh()->sensor_limit);
    }

    private function assertLimitsCleared(WirelessSensor $sensor): void
    {
        $this->assertNull($sensor->sensor_limit);
        $this->assertNull($sensor->sensor_limit_warn);
        $this->assertNull($sensor->sensor_limit_low_warn);
        $this->assertNull($sensor->sensor_limit_low);
    }

    public function testUserCannotResetCustomLimits(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device, ['sensor_custom' => 'Yes']);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.wireless-sensors.reset', $device))
            ->assertForbidden();

        $this->assertSame('Yes', $sensor->fresh()->sensor_custom);
    }
}
