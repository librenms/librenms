<?php

namespace LibreNMS\Tests\Feature\Discovery;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Discovery\FdbTable;
use LibreNMS\Tests\DBTestCase;
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

    public function testNewEntriesUseBoundedInsertsAcrossVlans(): void
    {
        $device = Device::factory()->create();
        $entries = [];
        for ($i = 1; $i <= 2051; $i++) {
            $entries[10][sprintf('%012x', $i)] = ['port_id' => 7];
        }
        $entries[20]['000000000001'] = [];
        $inserts = [];
        DB::listen(function (QueryExecuted $query) use (&$inserts): void {
            if (preg_match('/^insert into [`"]?ports_fdb\b/i', $query->sql)) {
                $inserts[] = count($query->bindings);
            }
        });

        FdbTable::sync($device->device_id, $entries, [], [10 => 100, 20 => 200]);

        $rows = DB::table('ports_fdb')->where('device_id', $device->device_id);
        $this->assertSame(2052, $rows->count());
        $this->assertSame([6000, 6000, 306, 6], $inserts);
        $this->assertSame(0, (clone $rows)->where('vlan_id', 20)->value('port_id'));
        $this->assertSame(0, (clone $rows)->whereNull('created_at')->count());
        $this->assertSame(0, (clone $rows)->whereNull('updated_at')->count());
        $messages = array_column($this->handler->getRecords(), 'message');
        $this->assertSame(2052, substr_count(implode('', $messages), '+'));
    }

    public function testExistingPortsTimestampsAndStaleRetentionArePreserved(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        $old = '2020-01-02 03:04:05';
        $now = '2026-01-02 03:04:05';
        $this->travelTo(Carbon::parse($now));
        $ids = [];
        foreach ([1, 2, 3, 4] as $i) {
            $ids[$i] = DB::table('ports_fdb')->insertGetId([
                'device_id' => $device->device_id, 'port_id' => 7,
                'vlan_id' => 10, 'mac_address' => sprintf('%012x', $i),
                'created_at' => $old, 'updated_at' => $old,
            ]);
        }
        $foreignId = DB::table('ports_fdb')->insertGetId([
            'device_id' => $other->device_id, 'port_id' => 9,
            'vlan_id' => 10, 'mac_address' => '000000000001',
            'created_at' => $old, 'updated_at' => $old,
        ]);
        $existing = [];
        foreach (DB::table('ports_fdb')->where('device_id', $device->device_id)->get() as $row) {
            $existing[$row->vlan_id][$row->mac_address] = (array) $row;
        }

        FdbTable::sync($device->device_id, [10 => [
            '000000000001' => ['port_id' => 8],
            '000000000002' => ['port_id' => 0],
            '000000000003' => ['port_id' => 7],
            '000000000005' => [],
        ]], $existing, []);

        foreach ([1 => 8, 2 => 7, 3 => 7] as $i => $portId) {
            $row = DB::table('ports_fdb')->where('ports_fdb_id', $ids[$i])->first();
            $this->assertSame($portId, $row->port_id);
            $this->assertSame($old, $row->created_at);
            $this->assertSame($now, $row->updated_at);
        }
        $this->assertSame($old, DB::table('ports_fdb')->where('ports_fdb_id', $ids[4])->value('updated_at'));
        $this->assertSame($old, DB::table('ports_fdb')->where('ports_fdb_id', $foreignId)->value('updated_at'));
        $this->assertSame(9, DB::table('ports_fdb')->where('ports_fdb_id', $foreignId)->value('port_id'));
        $this->assertSame(5, DB::table('ports_fdb')->where('device_id', $device->device_id)->count());

        FdbTable::sync($device->device_id, [], $existing, []);
        $this->assertSame(5, DB::table('ports_fdb')->where('device_id', $device->device_id)->count());
    }
}
