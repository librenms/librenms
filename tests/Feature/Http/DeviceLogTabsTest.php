<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;

class DeviceLogTabsTest extends TestCase
{
    use DatabaseTransactions;

    private const PAYLOAD = '"></option></select><img src=x onerror=alert(1)>';

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    /**
     * @return array<string, array{string, array<int, string>}>
     */
    public static function logTabs(): array
    {
        return [
            'syslog' => ['device.syslog', ['program', 'priority', 'from', 'to']],
            'eventlog' => ['device.eventlog', ['eventtype']],
            'graylog' => ['device.graylog', ['stream']],
        ];
    }

    /**
     * @param  array<int, string>  $params
     */
    #[DataProvider('logTabs')]
    public function testFilterParametersAreHtmlEscapedInsideJavascript(string $route, array $params): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');
        $device = Device::factory()->create();

        $query = array_fill_keys($params, self::PAYLOAD);

        $response = $this->actingAs($admin)
            ->get(route($route, ['device' => $device] + $query))
            ->assertOk();

        $flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

        // values concatenated into HTML strings must be html escaped before being JS encoded
        $response->assertDontSee('+ ' . json_encode(self::PAYLOAD, $flags) . ' +', false);
        $response->assertSee('+ ' . json_encode(e(self::PAYLOAD), $flags) . ' +', false);
    }

    public function testGlobalGraylogStreamIsEscaped(): void
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/graylog?stream=' . urlencode(self::PAYLOAD))
            ->assertOk();

        $response->assertDontSee(self::PAYLOAD, false);
        $response->assertSee('+ ' . json_encode(htmlspecialchars(self::PAYLOAD)) . ' +', false);
    }
}
