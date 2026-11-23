<?php

namespace LibreNMS\Tests\Feature\Graphs\Vminfo;

use App\Data\Graphing\GraphParameters;
use App\Graphs\GraphAccess;
use App\Graphs\GraphFactory;
use App\Graphs\Vminfo\CpuGraph;
use App\Graphs\Vminfo\DiskioBitsGraph;
use App\Graphs\Vminfo\MemGraph;
use App\Graphs\Vminfo\NetGraph;
use App\Models\Device;
use App\Models\User;
use App\Models\Vminfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use Mockery;

final class VminfoGraphTest extends TestCase
{
    use RefreshDatabase;

    public function testGraphTypesResolveToClasses(): void
    {
        $factory = app(GraphFactory::class);

        $this->assertInstanceOf(CpuGraph::class, $factory->graphFor(new GraphParameters(['type' => 'vminfo_cpu', 'id' => 1])));
        $this->assertInstanceOf(DiskioBitsGraph::class, $factory->graphFor(new GraphParameters(['type' => 'vminfo_diskio_bits', 'id' => 1])));
        $this->assertEqualsCanonicalizing(['cpu', 'mem', 'disk', 'diskio_bits', 'diskio_ops', 'net'], $factory->subtypes('vminfo'));
    }

    public function testSeriesUseTheMetricsOfTheVm(): void
    {
        $device = Device::factory()->create(['hostname' => 'pve.example.com']);
        $vm = Vminfo::factory()->for($device)->create(['vm_type' => 'proxmox', 'vmwVmVMID' => 100]);

        $mem = new MemGraph(new GraphParameters(['type' => 'vminfo_mem', 'id' => $vm->id]))->getSeries();
        $this->assertSame(['used', 'total'], array_keys($mem));
        $this->assertEquals($vm->metricIdentity('vm.memory'), $mem['used']->metric);

        $net = new NetGraph(new GraphParameters(['type' => 'vminfo_net', 'id' => $vm->id]))->getSeries();
        $this->assertSame(8.0, $net['in']->scale);
        $this->assertFalse($net['in']->style->invert);
        $this->assertTrue($net['out']->style->invert);
    }

    public function testTitleAndPageContext(): void
    {
        $device = Device::factory()->create();
        $vm = Vminfo::factory()->for($device)->create(['vmwVmDisplayName' => 'web']);

        $graph = new CpuGraph(new GraphParameters(['type' => 'vminfo_cpu', 'id' => $vm->id]));

        $this->assertSame($device->shortDisplayName() . '::web', $graph->getTitle());
        $this->assertSame($device->device_id, $graph->getPageContext()->device?->device_id);
        $this->assertSame(' :: VM :: web', $graph->getPageContext()->subtitle);
    }

    public function testMissingVmHasNoData(): void
    {
        $graph = new CpuGraph(new GraphParameters(['type' => 'vminfo_cpu', 'id' => 999999]));

        $this->assertFalse(app(GraphAccess::class)->trusted(fn () => $graph->authorize()));

        $this->expectException(NoGraphData::class);
        $graph->getSeries();
    }

    public function testAuthorizesBasedOnTheVm(): void
    {
        $vm = Vminfo::factory()->create();
        $graph = new CpuGraph(new GraphParameters(['type' => 'vminfo_cpu', 'id' => $vm->id]));

        $this->actingAs(User::factory()->create());

        Gate::shouldReceive('allows')
            ->with('view', Mockery::on(fn ($model) => $model instanceof Vminfo && $model->id === $vm->id))
            ->once()
            ->andReturn(false);

        $this->assertFalse($graph->authorize());
    }
}
