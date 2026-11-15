<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Js;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @return array<string, array{string, bool, array<int, string>}>
     */
    public static function logPages(): array
    {
        return [
            'device syslog' => ['device.syslog', true, []],
            'device eventlog' => ['device.eventlog', true, ['eventtype']],
            'device graylog' => ['device.graylog', true, []],
            'global syslog' => ['syslog', false, []],
            'global eventlog' => ['eventlog', false, ['eventtype']],
            'global graylog' => ['graylog', false, []],
        ];
    }

    /**
     * @param  array<int, string>  $params
     */
    #[DataProvider('logPages')]
    public function testFilterParametersAreEscaped(string $route, bool $deviceTab, array $params): void
    {
        $device = Device::factory()->create();
        $query = array_fill_keys($params, self::PAYLOAD);
        $parameters = ($deviceTab ? ['device' => $device] : ['device' => $device->device_id]) + $query;

        $response = $this->actingAs($this->admin())
            ->get(route($route, $parameters))
            ->assertOk()
            ->assertDontSee(self::PAYLOAD, false);

        if ($params) {
            $response->assertSee((string) Js::from(self::PAYLOAD), false);
        }
    }

    public function testGlobalPagesPreselectTheFilteredDevice(): void
    {
        $device = Device::factory()->create();

        foreach (['eventlog', 'graylog'] as $route) {
            $this->actingAs($this->admin())
                ->get(route($route, ['device' => $device->device_id]))
                ->assertOk()
                ->assertSee((string) Js::from(['id' => $device->device_id, 'text' => $device->display]), false);
        }
    }

    public function testSyslogFilterBarIsEscaped(): void
    {
        $device = Device::factory()->create();

        foreach ([route('syslog'), route('device.syslog', ['device' => $device])] as $url) {
            $this->actingAs($this->admin())
                ->get($url . '?' . http_build_query(['filter' => ['program' => ['eq' => self::PAYLOAD]]]))
                ->assertOk()
                ->assertDontSee(self::PAYLOAD, false)
                ->assertSee('filterBarComponent', false);
        }
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
