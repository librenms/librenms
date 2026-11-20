<?php

namespace LibreNMS\Tests\Feature\Graphs\Storage;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphFactory;
use App\Graphs\Mempool\UsageGraph as MempoolUsageGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Graphs\Storage\UsageGraph as StorageUsageGraph;
use App\Models\Device;
use App\Models\Mempool;
use App\Models\Storage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Tests\TestCase;
use Mockery;

class UsageGraphsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('host/usage.rrd');
    }

    public function test_storage_stacks_used_and_free_bytes(): void
    {
        $storage = Storage::factory()->for(Device::factory())->create([
            'type' => 'hrstorage', 'storage_descr' => '/var', 'storage_units' => 4096, 'storage_size' => 1073741824, 'storage_perc' => 90, 'storage_perc_warn' => 80,
        ]);

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'storage_usage', 'id' => $storage->storage_id]));
        $this->assertInstanceOf(StorageUsageGraph::class, $graph);
        $this->assertSame(['hostname' => $storage->device->hostname, 'type' => 'hrstorage', 'descr' => '/var'], $graph->getSeries()['used']->metric->tags);
        $this->assertSame(' :: Storage :: /var', $graph->getPageContext()->subtitle);

        $command = $graph->getDebugCommand();
        $this->assertStringContainsString("'--base' '1024'", $command);
        $this->assertStringContainsString("'AREA:ds0#c75862:Used             '", $command);
        $this->assertStringContainsString("'AREA:ds1#D0D0D0:Free             :STACK'", $command);
        $this->assertStringContainsString('GPRINT:ds1:LAST:%5.2lf%sB', $command);
    }

    public function test_storage_with_only_a_percentage_stacks_to_100_percent(): void
    {
        $storage = Storage::factory()->for(Device::factory())->create(['storage_units' => 10, 'storage_size' => 1000]);

        $command = new StorageUsageGraph(new GraphParameters(['type' => 'storage_usage', 'id' => $storage->storage_id]))->getDebugCommand();

        $this->assertStringContainsString('CDEF:ds0=ds0raw,0.1,*', $command);
        $this->assertStringContainsString("'-u' '100'", $command);
        $this->assertStringNotContainsString("'--base'", $command);
        $this->assertStringContainsString('GPRINT:ds0:LAST:%5.2lf%s%%', $command);
    }

    public function test_mempool_stacks_used_and_free(): void
    {
        $mempool = Mempool::factory()->for(Device::factory())->create(['mempool_type' => 'hrstorage', 'mempool_class' => 'system', 'mempool_index' => 1, 'mempool_total' => 8589934592]);

        $graph = (new GraphFactory)->graphFor(new GraphParameters(['type' => 'mempool_usage', 'id' => $mempool->mempool_id]));
        $this->assertInstanceOf(MempoolUsageGraph::class, $graph);
        $this->assertSame(['hostname' => $mempool->device->hostname, 'type' => 'hrstorage', 'class' => 'system', 'index' => '1'], $graph->getSeries()['used']->metric->tags);

        $command = $graph->getDebugCommand();
        $this->assertStringContainsString("'--base' '1024'", $command);
        $this->assertStringContainsString('GPRINT:ds0:LAST:%5.2lf%sB', $command);
    }

    public function test_mempool_with_only_a_percentage_stacks_to_100_percent(): void
    {
        $mempool = Mempool::factory()->for(Device::factory())->create(['mempool_total' => 100]);

        $command = new MempoolUsageGraph(new GraphParameters(['type' => 'mempool_usage', 'id' => $mempool->mempool_id]))->getDebugCommand();

        $this->assertStringContainsString("'-u' '100'", $command);
        $this->assertStringContainsString('GPRINT:ds0:LAST:%5.2lf%s%%', $command);
    }

    public function test_users_need_permission_to_the_storage(): void
    {
        $storage = Storage::factory()->for(Device::factory())->create();
        $this->actingAs(User::factory()->create());
        Gate::shouldReceive('allows')->with('view', Mockery::on(fn ($model) => $model instanceof Storage && $model->is($storage)))->once()->andReturn(false);

        $this->assertFalse(new StorageUsageGraph(new GraphParameters(['type' => 'storage_usage', 'id' => $storage->storage_id]))->authorize());
    }

    public function test_missing_ids_are_not_authorized(): void
    {
        $this->assertFalse(new MempoolUsageGraph(new GraphParameters(['type' => 'mempool_usage', 'id' => PHP_INT_MAX]))->authorize());
    }
}
