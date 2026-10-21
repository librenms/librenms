<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Component;
use App\Models\Device;
use App\Models\Package;
use App\Models\Processor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

class LegacyPageEscapingTest extends TestCase
{
    use DatabaseTransactions;

    private const PAYLOAD = '<img src=x onerror=alert(1)>';
    private const ESCAPED = '&lt;img src=x onerror=alert(1)&gt;';

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');

        // legacy pages emit warnings that production ignores
        set_error_handler(fn (int $severity): bool => ($severity & (E_NOTICE | E_WARNING)) !== 0);
    }

    protected function tearDown(): void
    {
        restore_error_handler();

        parent::tearDown();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testPackagesPagesEscapeDeviceSuppliedData(): void
    {
        $device = Device::factory()->create();
        Package::factory()->for($device)->create([
            'name' => self::PAYLOAD,
            'version' => self::PAYLOAD,
        ]);

        $this->actingAs($this->admin())
            ->get('/packages')
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false)
            ->assertSee(self::ESCAPED, false);

        $this->actingAs($this->admin())
            ->get('/search/search=packages')
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false)
            ->assertSee(self::ESCAPED, false);
    }

    public function testProcessorPageEscapesDescription(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->create([
            'device_id' => $device->device_id,
            'processor_descr' => self::PAYLOAD,
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'health', 'vars' => 'metric=processor']))
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false)
            ->assertSee(self::ESCAPED, false);
    }

    public function testF5LoadBalancerPagesEscapeComponentLabels(): void
    {
        $device = Device::factory()->create(['os' => 'f5']);
        $vs = Component::factory()->create([
            'device_id' => $device->device_id,
            'type' => 'f5-ltm-vs',
            'label' => self::PAYLOAD,
            'status' => 0,
            'error' => '',
        ]);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'loadbalancer', 'vars' => 'type=ltm_vs']))
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false)
            ->assertSee(self::ESCAPED, false);

        $this->actingAs($this->admin())
            ->get(route('device', ['device' => $device, 'tab' => 'loadbalancer', 'vars' => "type=ltm_vs/subtype=ltm_vs_det/vsid=$vs->id"]))
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false)
            ->assertSee(self::ESCAPED, false);
    }
}
