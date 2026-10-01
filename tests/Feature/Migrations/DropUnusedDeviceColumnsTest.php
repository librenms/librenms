<?php

namespace LibreNMS\Tests\Feature\Migrations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Tests\InMemoryDbTestCase;

final class DropUnusedDeviceColumnsTest extends InMemoryDbTestCase
{
    public function testPingLatencyRulesMoveToDeviceStats(): void
    {
        $this->artisan('migrate:rollback', ['--database' => $this->connection, '--step' => 1]);
        $this->assertTrue(Schema::hasColumn('devices', 'last_ping_timetaken'));

        DB::table('alert_rules')->insert([
            'severity' => 'warning',
            'extra' => '{}',
            'disabled' => 0,
            'name' => 'Ping Latency',
            'builder' => '{"condition":"AND","rules":[{"id":"devices.last_ping_timetaken","field":"devices.last_ping_timetaken","type":"string","input":"text","operator":"greater","value":"10"}],"valid":true}',
            'query' => 'SELECT * FROM devices WHERE (devices.device_id = ?) AND devices.last_ping_timetaken > 10',
        ]);

        $this->artisan('migrate', ['--database' => $this->connection]);

        foreach (['last_ping', 'last_ping_timetaken', 'last_poll_attempted', 'agent_uptime'] as $column) {
            $this->assertFalse(Schema::hasColumn('devices', $column), "$column not dropped");
        }

        $rule = DB::table('alert_rules')->where('name', 'Ping Latency')->first();
        $this->assertStringContainsString('"field":"device_stats.ping_rtt_last"', $rule->builder);
        $this->assertStringNotContainsString('last_ping_timetaken', $rule->builder . $rule->query);
        $this->assertSame(
            'SELECT * FROM devices WHERE (devices.device_id = ?) AND (SELECT ping_rtt_last FROM device_stats WHERE device_stats.device_id = devices.device_id) > 10',
            $rule->query
        );
        $this->assertSame([], DB::select($rule->query, [1])); // valid SQL
    }
}
