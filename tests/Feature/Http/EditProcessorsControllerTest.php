<?php

namespace LibreNMS\Tests\Feature\Http;

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
            'processor_perc_warn' => 85,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.processors', $device))
            ->assertOk()
            ->assertSee('CPU &lt;b&gt;0&lt;/b&gt;', false)
            ->assertDontSee('CPU <b>0</b>', false)
            ->assertSee('42%')
            ->assertSee('value="85"', false);
    }

    public function testNullWarnThresholdRendersEmpty(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->for($device)->create(['processor_perc_warn' => null]);

        $this->actingAs($this->admin())
            ->get(route('device.edit.processors', $device))
            ->assertOk()
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
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn' => '90'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame(90, $processor->fresh()->processor_perc_warn);
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

    #[DataProvider('invalidThresholds')]
    public function testInvalidWarnThresholdIsRejected(mixed $value): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn' => $value])
            ->assertUnprocessable();

        $this->assertSame(75, $processor->fresh()->processor_perc_warn);
    }

    public function testProcessorMustBelongToDevice(): void
    {
        $device = Device::factory()->create();
        $otherProcessor = Processor::factory()->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->admin())
            ->postJson(route('device.edit.processors.update', [$device, $otherProcessor]), ['processor_perc_warn' => 90])
            ->assertNotFound();

        $this->assertSame(75, $otherProcessor->fresh()->processor_perc_warn);
    }

    public function testUserCannotUpdateWarnThreshold(): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->for($device)->create(['processor_perc_warn' => 75]);

        $this->actingAs($this->user())
            ->postJson(route('device.edit.processors.update', [$device, $processor]), ['processor_perc_warn' => 90])
            ->assertForbidden();

        $this->assertSame(75, $processor->fresh()->processor_perc_warn);
    }
}
