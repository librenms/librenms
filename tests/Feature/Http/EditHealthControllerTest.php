<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class EditHealthControllerTest extends TestCase
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
    private function sensor(Device $device, array $attributes = []): Sensor
    {
        return Sensor::factory()->for($device)->create(array_merge([
            'sensor_class' => 'temperature',
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

    public function testAdminCanViewHealthPage(): void
    {
        $device = Device::factory()->create();
        $this->sensor($device);
        $this->sensor($device, ['sensor_descr' => 'Deleted sensor', 'sensor_deleted' => 1]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.health', $device))
            ->assertOk()
            ->assertSee('Radio &lt;b&gt;1&lt;/b&gt;', false)
            ->assertDontSee('Radio <b>1</b>', false)
            ->assertDontSee('Deleted sensor')
            ->assertSee('value="50"', false)
            ->assertSee('value="40"', false)
            ->assertSee('value="5"', false)
            ->assertSee('value="1"', false);
    }

    public function testUserCannotViewHealthPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.health', $device))
            ->assertForbidden();
    }

    public function testAdminCanUpdateLimit(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_limit_warn' => '45.5'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $sensor->refresh();
        $this->assertEquals(45.5, $sensor->sensor_limit_warn);
        $this->assertSame('Yes', $sensor->sensor_custom);
    }

    public function testCustomLimitIsKeptWhenUpdatingAlert(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device, ['sensor_custom' => 'Yes', 'sensor_limit' => 70]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_alert' => 0])
            ->assertOk();

        $sensor->refresh();
        $this->assertEquals(70, $sensor->sensor_limit);
        $this->assertEquals(0, $sensor->sensor_alert);
        $this->assertSame('Yes', $sensor->sensor_custom);
    }

    public function testEmptyLimitClearsIt(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_limit_low' => ''])
            ->assertOk();

        $this->assertNull($sensor->fresh()->sensor_limit_low);
    }

    public function testInvalidLimitIsRejected(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_limit' => 'abc'])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_current' => '1'])
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
            ->postJson(route('device.edit.health.update', [$device, $sensor]), [])
            ->assertUnprocessable();

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_custom' => 'Yes'])
            ->assertUnprocessable();

        $this->assertSame('No', $sensor->fresh()->sensor_custom);
    }

    public function testSensorMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherSensor = $this->sensor(Device::factory()->create());

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $otherSensor]), ['sensor_limit' => 99])
            ->assertNotFound();

        $this->assertEquals(50, $otherSensor->fresh()->sensor_limit);
    }

    public function testUserCannotUpdateLimit(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_limit' => 99])
            ->assertForbidden();

        $this->assertEquals(50, $sensor->fresh()->sensor_limit);
    }

    public function testAdminCanToggleAlerts(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_alert' => 0])
            ->assertOk()
            ->assertJson(['status' => 'ok']);
        $this->assertEquals(0, $sensor->fresh()->sensor_alert);
        $this->assertSame('No', $sensor->fresh()->sensor_custom);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_alert' => 1])
            ->assertOk()
            ->assertJson(['status' => 'ok']);
        $this->assertEquals(1, $sensor->fresh()->sensor_alert);
    }

    public function testUserCannotToggleAlerts(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_alert' => 0])
            ->assertForbidden();

        $this->assertEquals(1, $sensor->fresh()->sensor_alert);
    }

    public function testAdminCanResetCustomLimit(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device, ['sensor_custom' => 'Yes']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.update', [$device, $sensor]), ['sensor_custom' => 'No'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame('Reset', $sensor->fresh()->sensor_custom);
    }

    public function testAdminCanResetAllCustomLimits(): void
    {
        $device = Device::factory()->create();
        $first = $this->sensor($device, ['sensor_custom' => 'Yes']);
        $second = $this->sensor($device, ['sensor_custom' => 'Yes']);
        $other = $this->sensor(Device::factory()->create(), ['sensor_custom' => 'Yes']);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.health.reset', $device))
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame('Reset', $first->fresh()->sensor_custom);
        $this->assertSame('Reset', $second->fresh()->sensor_custom);
        $this->assertSame('Yes', $other->fresh()->sensor_custom);
    }

    public function testUserCannotResetCustomLimits(): void
    {
        $device = Device::factory()->create();
        $sensor = $this->sensor($device, ['sensor_custom' => 'Yes']);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.health.reset', $device))
            ->assertForbidden();

        $this->assertSame('Yes', $sensor->fresh()->sensor_custom);
    }
}
