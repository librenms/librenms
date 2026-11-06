<?php

namespace LibreNMS\Tests\Feature\Discovery;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use App\Models\Vlan;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;

final class FdbTableTest extends DBTestCase
{
    use DatabaseTransactions;

    private TestHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new TestHandler;
        $handler = $this->handler;
        Log::extend('capture-progress', fn () => new MonologLogger('progress', [$handler]));
        config(['logging.default' => 'stdout', 'logging.channels.stdout' => ['driver' => 'capture-progress']]);
        Log::forgetChannel('stdout');
    }

    public function testNewEntriesAreBatchedAcrossVlans(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        $port = Port::factory()->for($device)->create(['ifIndex' => 7]);
        $vlan = Vlan::factory()->for($device)->create(['vlan_vlan' => 10]);
        $otherVlan = Vlan::factory()->for($device)->create(['vlan_vlan' => 20]);
        $entries = [];
        for ($i = 1; $i <= 2051; $i++) {
            $entries[10][sprintf('%012x', $i)] = 7;
        }
        $entries[20]['000000000001'] = 999; // Unknown interface maps to port zero.
        $inserts = [];
        DB::listen(function (QueryExecuted $query) use (&$inserts): void {
            if (str_starts_with($query->sql, 'insert into') && str_contains($query->sql, 'ports_fdb')) {
                $inserts[] = count($query->bindings);
            }
        });

        $this->discover($device, $entries);

        $rows = DB::table('ports_fdb')->where('device_id', $device->device_id);
        $this->assertSame(2052, $rows->count());
        $this->assertSame([6000, 6000, 306, 6], $inserts);
        $this->assertSame(2051, (clone $rows)->where('vlan_id', $vlan->vlan_id)->where('port_id', $port->port_id)->count());
        $this->assertSame(0, (clone $rows)->where('vlan_id', $otherVlan->vlan_id)->value('port_id'));
        $this->assertSame(0, (clone $rows)->whereNull('created_at')->count());
        $this->assertSame(0, (clone $rows)->whereNull('updated_at')->count());
        $messages = array_column($this->handler->getRecords(), 'message');
        $this->assertSame(2052, substr_count(implode('', $messages), '+'));
    }

    public function testExistingPortsTimestampsAndStaleRetentionArePreserved(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        $other = Device::factory()->create();
        $port = Port::factory()->for($device)->create(['ifIndex' => 7]);
        $movedPort = Port::factory()->for($device)->create(['ifIndex' => 8]);
        $vlan = Vlan::factory()->for($device)->create(['vlan_vlan' => 10]);
        $old = '2020-01-02 03:04:05';
        $now = '2026-01-02 03:04:05';
        $this->travelTo(Carbon::parse($now));
        $ids = [];
        foreach ([1, 2, 3, 4] as $i) {
            $ids[$i] = DB::table('ports_fdb')->insertGetId([
                'device_id' => $device->device_id, 'port_id' => $port->port_id,
                'vlan_id' => $vlan->vlan_id, 'mac_address' => sprintf('%012x', $i),
                'created_at' => $old, 'updated_at' => $old,
            ]);
        }
        $foreignId = DB::table('ports_fdb')->insertGetId([
            'device_id' => $other->device_id, 'port_id' => 9,
            'vlan_id' => $vlan->vlan_id, 'mac_address' => '000000000001',
            'created_at' => $old, 'updated_at' => $old,
        ]);

        $this->discover($device, [10 => [
            '000000000001' => 8,
            '000000000002' => 999,
            '000000000003' => 7,
            '000000000005' => 999,
        ]]);

        foreach ([1 => $movedPort->port_id, 2 => $port->port_id, 3 => $port->port_id] as $i => $portId) {
            $row = DB::table('ports_fdb')->where('ports_fdb_id', $ids[$i])->first();
            $this->assertSame($portId, $row->port_id);
            $this->assertSame($old, $row->created_at);
            $this->assertSame($now, $row->updated_at);
        }
        $this->assertSame($old, DB::table('ports_fdb')->where('ports_fdb_id', $ids[4])->value('updated_at'));
        $this->assertSame($old, DB::table('ports_fdb')->where('ports_fdb_id', $foreignId)->value('updated_at'));
        $this->assertSame(9, DB::table('ports_fdb')->where('ports_fdb_id', $foreignId)->value('port_id'));
        $new = DB::table('ports_fdb')->where('device_id', $device->device_id)->where('mac_address', '000000000005')->sole();
        $this->assertSame(0, $new->port_id);
        $this->assertSame($now, $new->created_at);
        $this->assertSame($now, $new->updated_at);

        $this->discover($device, []);
        $this->assertSame(5, DB::table('ports_fdb')->where('device_id', $device->device_id)->count());
    }

    /**
     * Run the real discovery include with a mocked SNMP backend.
     *
     * @param  array<int, array<string, int>>  $entries  VLAN => MAC => ifIndex
     */
    private function discover(Device $model, array $entries): void
    {
        $output = [];
        foreach ($entries as $vlan => $macs) {
            foreach ($macs as $mac => $ifIndex) {
                $mac = implode(':', str_split($mac, 2));
                $output[] = "dot1qTpFdbPort[$vlan][$mac] = $ifIndex";
            }
        }
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(fn (string $target, string $oid) => new RawSnmpResponse($oid === 'dot1qTpFdbPort' ? implode("\n", $output) : ''));
        $this->app->instance(SnmpBackendInterface::class, $backend);

        try {
            DeviceCache::setPrimary($model->device_id);
            $device = $model->refresh()->toArray();
            include base_path('includes/discovery/fdb-table.inc.php');
        } finally {
            DeviceCache::flush();
        }
    }
}
