<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Processor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;

final class EditProcessorsControllerTest extends TestCase
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

    public function testAdminCanViewProcessorsPage(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->for($device)->create([
            'processor_descr' => 'CPU <b>0</b>',
            'processor_usage' => 42,
            'processor_perc_warn_custom' => 85,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.processors', $device))
            ->assertOk()
            ->assertSee('CPU &lt;b&gt;0&lt;/b&gt;', false)
            ->assertDontSee('CPU <b>0</b>', false)
            ->assertSee('42%')
            ->assertSee('value="85"', false);
    }

    public function testDiscoveredThresholdIsShownAsPlaceholder(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->for($device)->create(['processor_perc_warn' => 80]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.processors', $device))
            ->assertOk()
            ->assertSee('placeholder="80"', false)
            ->assertSee('value=""', false);
    }

    public function testUserCannotViewProcessorsPage(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->get(route('device.edit.processors', $device))
            ->assertForbidden();
    }

    public function testAdminCanUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn_custom' => '90'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $processor = $processor->fresh();
        $this->assertSame(90, $processor->processor_perc_warn_custom);
        $this->assertSame(90, $processor->processor_perc_warn);
    }

    public function testAdminCanClearWarnThreshold(): void
    {
        LibrenmsConfig::set('processor_perc_warn', 70);
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn_custom' => 90]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn_custom' => ''])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $processor = $processor->fresh();
        $this->assertNull($processor->processor_perc_warn_custom);
        $this->assertSame(70, $processor->processor_perc_warn);
    }

    public function testRediscoveryKeepsUserThreshold(): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn_custom' => 90, 'processor_usage' => 10]);

        // discovery merges freshly discovered attributes, including a null or default threshold
        $processor->processor_perc_warn = 80;
        $processor->processor_usage = 20;
        $processor->save();

        $processor = $processor->fresh();
        $this->assertSame(90, $processor->processor_perc_warn);
        $this->assertEquals(20, $processor->processor_usage);
    }

    public function testRediscoveryUpdatesDiscoveredThreshold(): void
    {
        LibrenmsConfig::set('processor_perc_warn', 70);
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn' => 80]);

        // device reports a new threshold
        $processor->processor_perc_warn = 95;
        $processor->save();
        $this->assertSame(95, $processor->fresh()->processor_perc_warn);

        // device no longer reports a threshold
        $processor->processor_perc_warn = null;
        $processor->save();
        $this->assertSame(70, $processor->fresh()->processor_perc_warn);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidThresholds(): array
    {
        return [
            'non-numeric' => ['abc'],
            'negative' => [-1],
            'over 100' => [101],
        ];
    }

    #[DataProvider('invalidThresholds')]
    public function testInvalidWarnThresholdIsRejected(mixed $value): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn_custom' => $value])
            ->assertUnprocessable();

        $this->assertSame(75, $processor->fresh()->processor_perc_warn);
    }

    public function testProcessorMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherProcessor = Processor::factory()->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.processors.update', [$device, $otherProcessor]), ['processor_perc_warn_custom' => 90])
            ->assertNotFound();

        $this->assertSame(75, $otherProcessor->fresh()->processor_perc_warn);
    }

    public function testUserCannotUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn_custom' => 90])
            ->assertForbidden();

        $this->assertSame(75, $processor->fresh()->processor_perc_warn);
    }
}
