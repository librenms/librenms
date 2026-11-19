<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Location;
use App\Models\PollerGroup;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\AlertLogState;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Url;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class LegacyTableEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    public function testTableRoutesRequireAuthentication(): void
    {
        foreach (['arp-search', 'poll-log', 'alertlog-stats'] as $table) {
            $this->postJson(route("table.$table"))->assertUnauthorized();
        }
    }

    public function testArpSearchSupportsPortOnlyAccessAndRemoteLinks(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifInErrors_delta' => 1]);
        $otherPort = Port::factory()->for($device)->create();
        $remoteDevice = Device::factory()->create();
        $remotePort = Port::factory()->for($remoteDevice)->create(['ifPhysAddress' => '02aaab000001']);
        Ipv4Address::factory()->for($remotePort)->create(['ipv4_address' => '192.0.2.10']);
        Ipv4Mac::factory()->for($device)->for($port)->create([
            'ipv4_address' => '192.0.2.10',
            'mac_address' => '02aaab000001',
        ]);
        Ipv4Mac::factory()->for($device)->for($otherPort)->create([
            'ipv4_address' => '192.0.2.20',
            'mac_address' => '02aaab000002',
        ]);
        DB::table('vendor_ouis')->updateOrInsert(['oui' => '02aaab'], ['vendor' => 'Test vendor']);

        $user = $this->limitedUser();
        $user->portsOwned()->attach($port);
        $user->devicesOwned()->attach($remoteDevice);
        $this->actingAs($user);

        $response = $this->postJson(route('table.arp-search'), [
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'searchPhrase' => '02:aa:ab',
            'searchby' => 'mac',
            'rowCount' => -1,
        ])
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.ipv4_address', '192.0.2.10')
            ->assertJsonPath('rows.0.mac_address', '02:aa:ab:00:00:01')
            ->assertJsonPath('rows.0.mac_oui', 'Test vendor');

        $this->assertStringContainsString('fa-flag', $response->json('rows.0.interface'));
        $this->assertStringContainsString(Url::deviceUrl($remoteDevice), $response->json('rows.0.remote_device'));
        $this->assertStringContainsString(Url::portUrl($remotePort), $response->json('rows.0.remote_interface'));

        $this->postJson(route('table.arp-search'), [
            'device_id' => $device->device_id,
            'searchPhrase' => '192.0.2.',
            'rowCount' => -1,
        ])
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.ipv4_address', '192.0.2.10');

        $this->postJson(route('table.arp-search'), [
            'device_id' => $device->device_id,
            'port_id' => $otherPort->port_id,
            'rowCount' => -1,
        ])
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('rows', []);
    }

    public function testDeviceArpSearchSortsByInterface(): void
    {
        $device = Device::factory()->create();
        $first = Port::factory()->for($device)->create(['ifDescr' => 'Interface A']);
        $second = Port::factory()->for($device)->create(['ifDescr' => 'Interface B', 'ifPhysAddress' => '02aaab000003']);
        Ipv4Address::factory()->for($second)->create(['ipv4_address' => '192.0.2.20']);
        Ipv4Mac::factory()->for($device)->for($first)->create(['ipv4_address' => '192.0.2.10']);
        Ipv4Mac::factory()->for($device)->for($second)->create([
            'ipv4_address' => '192.0.2.20',
            'mac_address' => '02aaab000003',
        ]);
        $user = $this->limitedUser();
        $user->devicesOwned()->attach($device);

        $this->actingAs($user)
            ->postJson(route('table.arp-search'), [
                'device_id' => $device->device_id,
                'port_id' => '',
                'sort' => ['interface' => 'desc'],
                'rowCount' => -1,
            ])
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('rows.0.ipv4_address', '192.0.2.20')
            ->assertJsonPath('rows.0.remote_device', 'Localhost')
            ->assertJsonPath('rows.0.remote_interface', 'Local port')
            ->assertJsonPath('rows.1.ipv4_address', '192.0.2.10');
    }

    public function testPollLogAppliesPagePermissionVisibilityAndOverdueThreshold(): void
    {
        LibrenmsConfig::set('rrd.step', 1000);
        $databaseNow = Carbon::parse(DB::scalar('SELECT NOW()'), 'UTC')->toImmutable();
        $active = ['status' => 1, 'last_polled' => $databaseNow->subSeconds(1300)];
        $group = PollerGroup::factory()->create(['group_name' => 'East']);
        $location = Location::factory()->create(['location' => 'Test & Lab']);
        $overdue = Device::factory()->for($location)->create($active + ['last_polled_timetaken' => 2.345, 'poller_group' => $group->id]);
        $recent = Device::factory()->create([
            'status' => 1,
            'last_polled' => $databaseNow->subSeconds(1100),
            'last_polled_timetaken' => 7.532,
        ]);
        $ignored = Device::factory()->create($active + ['ignore' => 1]);
        $disabled = Device::factory()->create($active + ['disabled' => 1]);
        $down = Device::factory()->create(['status' => 0] + $active);
        Device::factory()->create($active + ['last_polled_timetaken' => 10]);

        $user = $this->limitedUser();
        $user->devicesOwned()->attach([$overdue->device_id, $recent->device_id, $ignored->device_id, $disabled->device_id, $down->device_id]);
        $this->actingAs($user)->postJson(route('table.poll-log'))->assertForbidden();

        $user->givePermissionTo(Permission::findOrCreate('poller.view'));
        $this->postJson(route('table.poll-log'), ['rowCount' => -1])
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('rows.0.poller_group', 'General')
            ->assertJsonPath('rows.0.last_polled_timetaken', 7.53)
            ->assertJsonPath('rows.1.poller_group', 'East')
            ->assertJsonPath('rows.1.location', 'Test &amp; Lab');

        $response = $this->postJson(route('table.poll-log'), ['type' => 'unpolled', 'rowCount' => -1])
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.last_polled_timetaken', 2.35);

        $this->assertStringContainsString(Url::deviceUrl($overdue, ['tab' => 'graphs', 'group' => 'poller']), $response->json('rows.0.hostname'));
    }

    public function testPollLogSearchesLocalPollTimesAndSortsByLastPoll(): void
    {
        $originalTimezone = DB::scalar('SELECT @@session.time_zone');
        DB::statement("SET time_zone = '+00:00'");

        try {
            $older = Device::factory()->create(['status' => 1, 'last_polled' => '2024-01-01 22:12:00', 'last_polled_timetaken' => 4]);
            $newer = Device::factory()->create(['status' => 1, 'last_polled' => '2024-01-01 23:15:00', 'last_polled_timetaken' => 3]);
            $user = $this->limitedUser();
            $user->givePermissionTo(Permission::findOrCreate('poller.view'));
            $user->devicesOwned()->attach([$older->device_id, $newer->device_id]);

            $response = $this->actingAs($user)
                ->withSession(['preferences.timezone' => '+05:30'])
                ->postJson(route('table.poll-log'), [
                    'searchPhrase' => '2024-01-02',
                    'sort' => ['last_polled' => 'desc'],
                ])
                ->assertOk()
                ->assertJsonPath('total', 2)
                ->assertJsonPath('rows.0.last_polled_timetaken', 3)
                ->assertJsonPath('rows.1.last_polled_timetaken', 4);

            $this->assertStringContainsString(Url::deviceUrl($newer, ['tab' => 'graphs', 'group' => 'poller']), $response->json('rows.0.hostname'));
        } finally {
            DB::statement('SET time_zone = ?', [$originalTimezone]);
        }
    }

    public function testAlertLogStatsCountsVisibleDeviceAndRuleGroupsForPagingAndAll(): void
    {
        $first = Device::factory()->create(['sysName' => 'shared-name']);
        $second = Device::factory()->create(['sysName' => 'shared-name']);
        $hidden = Device::factory()->create(['sysName' => 'shared-name']);
        $critical = AlertRule::factory()->create(['severity' => 'critical']);
        $warning = AlertRule::factory()->create(['severity' => 'warning']);
        $recent = ['state' => AlertLogState::Active, 'time_logged' => now()->subDay()];
        AlertLog::factory()->for($first)->for($critical, 'rule')->count(2)->create($recent);
        AlertLog::factory()->for($first)->for($critical, 'rule')->create(['time_logged' => now()->subDays(8)] + $recent);
        AlertLog::factory()->for($first)->for($critical, 'rule')->create(['state' => AlertLogState::Recovered] + $recent);
        AlertLog::factory()->for($second)->for($critical, 'rule')->create($recent);
        AlertLog::factory()->for($hidden)->for($critical, 'rule')->create($recent);
        AlertLog::factory()->for($first)->for($warning, 'rule')->create($recent);
        $user = $this->limitedUser();
        $user->devicesOwned()->attach([$first->device_id, $second->device_id]);
        $this->actingAs($user);

        $filters = ['min_severity' => '6', 'time_interval' => '7', 'searchPhrase' => 'shared-name'];
        $this->postJson(route('table.alertlog-stats'), $filters + ['rowCount' => 1, 'current' => 2])
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('current', 2)
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.count', 1);

        $this->postJson(route('table.alertlog-stats'), $filters + ['rowCount' => -1])
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(2, 'rows')
            ->assertJsonPath('rows.0.count', 2)
            ->assertJsonPath('rows.0.alert_rule', $critical->name)
            ->assertJsonPath('rows.1.count', 1);

        $this->postJson(route('table.alertlog-stats'), [
            'min_severity' => 'warning only',
            'time_interval' => 7,
            'device_id' => $first->device_id,
        ])
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.alert_rule', $warning->name);
    }

    private function limitedUser(): User
    {
        return User::factory()->create(['enabled' => 1])->assignRole(Role::findOrCreate('user'));
    }
}
