<?php

namespace LibreNMS\Tests\Unit;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Tests\TestCase;

final class PortsFdbVlanMigrationTest extends TestCase
{
    private string $previousConnection;

    protected function setUp(): void
    {
        parent::setUp();

        // DDL can't be rolled back, so this runs on its own database
        $this->previousConnection = DB::getDefaultConnection();
        config(['database.connections.fdb_migration' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('fdb_migration');

        Schema::create('devices', function (Blueprint $table): void {
            $table->increments('device_id');
            $table->string('os')->nullable();
        });
        Schema::create('vlans', function (Blueprint $table): void {
            $table->increments('vlan_id');
            $table->unsignedInteger('device_id');
            $table->integer('vlan_vlan')->nullable();
        });
        Schema::create('ports_fdb', function (Blueprint $table): void {
            $table->bigIncrements('ports_fdb_id');
            $table->string('mac_address');
            $table->unsignedInteger('vlan_id')->index('ports_fdb_vlan_id_index');
            $table->unsignedInteger('device_id');
            $table->timestamp('updated_at')->nullable();
        });
    }

    protected function tearDown(): void
    {
        DB::purge('fdb_migration');
        DB::setDefaultConnection($this->previousConnection);

        parent::tearDown();
    }

    public function testVlanIdIsReplacedWithTheVlanNumber(): void
    {
        $vlan = DB::table('vlans')->insertGetId(['device_id' => 1, 'vlan_vlan' => 100]);
        $otherDevice = DB::table('vlans')->insertGetId(['device_id' => 2, 'vlan_vlan' => 200]);
        $rows = [
            'match' => $vlan,
            'no vlan' => 0,
            'id from another device' => $otherDevice,
            'vlan number stored as the id' => 4000,
        ];
        foreach ($rows as $mac => $vlanId) {
            DB::table('ports_fdb')->insert(['mac_address' => $mac, 'vlan_id' => $vlanId, 'device_id' => 1, 'updated_at' => '2026-01-01 00:00:00']);
        }

        $this->migration()->up();

        $this->assertFalse(Schema::hasColumn('ports_fdb', 'vlan_id'));
        $this->assertSame([
            'match' => 100,
            'no vlan' => 0,
            'id from another device' => 0,
            'vlan number stored as the id' => 0,
        ], DB::table('ports_fdb')->pluck('vlan', 'mac_address')->map(fn ($vlan) => (int) $vlan)->all());
        $this->assertSame(['2026-01-01 00:00:00'], DB::table('ports_fdb')->distinct()->pluck('updated_at')->all(), 'Last seen times are kept');
    }

    public function testDevicesThatStoredVlanNumbersAreNotLookedUp(): void
    {
        foreach (['edgeswitch', 'timos'] as $os) {
            $device = DB::table('devices')->insertGetId(['os' => $os]);
            $vlan = DB::table('vlans')->insertGetId(['device_id' => $device, 'vlan_vlan' => 500]);
            DB::table('ports_fdb')->insert(['mac_address' => $os, 'vlan_id' => $vlan, 'device_id' => $device]);
        }

        $this->migration()->up();

        $this->assertSame([0, 0], DB::table('ports_fdb')->pluck('vlan')->map(fn ($vlan) => (int) $vlan)->all());
    }

    public function testDownRestoresTheVlanId(): void
    {
        $vlan = DB::table('vlans')->insertGetId(['device_id' => 1, 'vlan_vlan' => 100]);
        DB::table('ports_fdb')->insert(['mac_address' => 'match', 'vlan_id' => $vlan, 'device_id' => 1]);

        $this->migration()->up();
        $this->migration()->down();

        $this->assertFalse(Schema::hasColumn('ports_fdb', 'vlan'));
        $this->assertSame($vlan, (int) DB::table('ports_fdb')->value('vlan_id'));
    }

    private function migration(): object
    {
        return require base_path('database/migrations/2026_11_04_000000_replace_ports_fdb_vlan_id_with_vlan.php');
    }
}
