<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Link;
use App\Models\Port;
use App\Models\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LibreNMS\Tests\TestCase;

class OrphanCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_a_device_deletes_its_local_links(): void
    {
        $device = Device::factory()->create();
        $link = Link::factory()->create([
            'local_device_id' => $device->device_id,
            'local_port_id' => null,
            'remote_device_id' => 0,
        ]);

        $device->delete();

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    public function test_deleting_a_device_deletes_links_pointing_at_it(): void
    {
        $local = Device::factory()->create();
        $remote = Device::factory()->create();
        $link = Link::factory()->create([
            'local_device_id' => $local->device_id,
            'local_port_id' => null,
            'remote_device_id' => $remote->device_id,
        ]);

        $remote->delete();

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    public function test_deleting_a_device_leaves_unrelated_links_alone(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        $keep = Link::factory()->create([
            'local_device_id' => $other->device_id,
            'local_port_id' => null,
            'remote_device_id' => 0,
        ]);

        $device->delete();

        $this->assertDatabaseHas('links', ['id' => $keep->id]);
    }

    public function test_deleting_a_port_still_deletes_its_links(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $link = Link::factory()->create([
            'local_device_id' => $device->device_id,
            'local_port_id' => $port->port_id,
            'remote_device_id' => 0,
        ]);

        $port->delete();

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    public function test_orphan_cleanup_does_not_read_the_devices_table(): void
    {
        Link::factory()->create([
            'local_device_id' => 99999,
            'local_port_id' => null,
            'remote_device_id' => 0,
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        Link::deleteOrphans();

        $deletes = array_values(array_filter($queries, fn ($sql) => str_starts_with(strtolower(ltrim($sql)), 'delete')));

        $this->assertNotEmpty($deletes, 'expected a delete statement');
        foreach ($deletes as $sql) {
            $this->assertStringNotContainsString('devices', $sql, "delete statement must not lock devices: $sql");
        }
    }

    public function test_orphan_cleanup_removes_links_whose_device_is_gone(): void
    {
        $link = Link::factory()->create([
            'local_device_id' => 99999,
            'local_port_id' => null,
            'remote_device_id' => 0,
        ]);

        Link::deleteOrphans();

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    public function test_orphan_cleanup_keeps_links_whose_device_exists(): void
    {
        $device = Device::factory()->create();
        $link = Link::factory()->create([
            'local_device_id' => $device->device_id,
            'local_port_id' => null,
            'remote_device_id' => 0,
        ]);

        Link::deleteOrphans();

        $this->assertDatabaseHas('links', ['id' => $link->id]);
    }

    public function test_processor_orphan_cleanup_does_not_read_the_devices_table(): void
    {
        Processor::factory()->create(['device_id' => 99999]);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        Processor::deleteOrphans();

        $deletes = array_values(array_filter($queries, fn ($sql) => str_starts_with(strtolower(ltrim($sql)), 'delete')));

        $this->assertNotEmpty($deletes, 'expected a delete statement');
        foreach ($deletes as $sql) {
            $this->assertStringNotContainsString('devices', $sql, "delete statement must not lock devices: $sql");
        }
    }

    public function test_processor_orphan_cleanup_removes_rows_whose_device_is_gone(): void
    {
        $orphan = Processor::factory()->create(['device_id' => 99999]);
        $kept = Processor::factory()->create();

        Processor::deleteOrphans();

        $this->assertDatabaseMissing('processors', ['processor_id' => $orphan->processor_id]);
        $this->assertDatabaseHas('processors', ['processor_id' => $kept->processor_id]);
    }
}
