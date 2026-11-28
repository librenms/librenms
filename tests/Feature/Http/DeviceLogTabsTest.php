<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Js;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DeviceLogTabsTest extends TestCase
{
    use DatabaseTransactions;

    private const string PAYLOAD = '"></option></select><img src=x onerror=alert(1)>';

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function filterBarPages(): array
    {
        return [
            'device syslog' => ['device.syslog', true, 'program'],
            'device eventlog' => ['device.eventlog', true, 'search'],
            'global syslog' => ['syslog', false, 'program'],
            'global eventlog' => ['eventlog', false, 'search'],
        ];
    }

    #[DataProvider('filterBarPages')]
    public function testFilterParametersAreEscaped(string $route, bool $deviceTab, string $field): void
    {
        $filter = [$field => ['eq' => self::PAYLOAD]];
        $parameters = ['filter' => $filter];
        if ($deviceTab) {
            $parameters['device'] = Device::factory()->create();
        }

        $this->actingAs($this->admin())
            ->get(route($route, $parameters))
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false)
            ->assertSee('filterBarComponent', false)
            ->assertSee((string) Js::from($filter), false);
    }

    public function testDeviceGraylogPageLoads(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.graylog', ['device' => $device]))
            ->assertOk();
    }

    public function testDeviceLogPagesRequireDeviceAccess(): void
    {
        $device = Device::factory()->create();
        $user = User::factory()->create(['enabled' => 1])->givePermissionTo(
            Permission::findOrCreate('eventlog.viewAll'),
            Permission::findOrCreate('syslog.viewAll'),
        );

        $this->actingAs($user);

        foreach (['eventlog', 'syslog', 'graylog'] as $page) {
            $this->get(route($page))->assertOk();
            $this->get(route('device.' . $page, $device))->assertForbidden();
        }
    }

    public function testGraylogPreselectsTheFilteredDevice(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('graylog', ['device' => $device->device_id]))
            ->assertOk()
            ->assertSee((string) Js::from(['id' => $device->device_id, 'text' => $device->display]), false);
    }

    public function testGraylogRejectsInvalidStream(): void
    {
        $this->actingAs($this->admin())
            ->get(route('graylog', ['stream' => self::PAYLOAD]))
            ->assertSessionHasErrors('stream');
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
