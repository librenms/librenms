<?php

namespace LibreNMS\Tests\Feature\Graphs\Device;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\Device\ProcessorGraph;
use App\Graphs\GraphAccess;
use App\Models\Device;
use App\Models\Processor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use Mockery;

class ProcessorGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturnUsing(fn ($key, $default = null) => $default);
        LibrenmsConfig::shouldReceive('get')->with('mono_font')->byDefault()->andReturn('DejaVuSansMono');
        LibrenmsConfig::shouldReceive('get')->with('display_sysname')->byDefault()->andReturn('hostname');
        LibrenmsConfig::shouldReceive('getOsSetting')->byDefault()->andReturn(false);
    }

    public function test_it_returns_correct_series(): void
    {
        $device = Device::factory()->create();
        $processor = Processor::factory()->create([
            'device_id' => $device->device_id,
            'processor_type' => 'hr',
            'processor_index' => 0,
            'processor_descr' => 'CPU 0',
        ]);

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));

        $series = $graph->getSeries();

        $this->assertCount(1, $series);
        $this->assertArrayHasKey("proc_{$processor->processor_id}", $series);
        $this->assertEquals('usage', $series["proc_{$processor->processor_id}"]->field);
        $this->assertEquals('processor', $series["proc_{$processor->processor_id}"]->metric->measurement);
        $this->assertEquals($device->hostname, $series["proc_{$processor->processor_id}"]->metric->tags['hostname']);
    }

    public function test_it_returns_a_series_per_processor(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->count(2)->sequence(['processor_index' => 0], ['processor_index' => 1])->create([
            'device_id' => $device->device_id,
            'processor_type' => 'hr',
        ]);

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));
        $metrics = array_map(fn ($series) => $series->metric, array_values($graph->getSeries()));

        $this->assertCount(2, $metrics);
        $this->assertEquals(['hostname' => $device->hostname, 'type' => 'hr', 'index' => 0], $metrics[0]->tags);
        $this->assertEquals(['hostname' => $device->hostname, 'type' => 'hr', 'index' => 1], $metrics[1]->tags);
    }

    public function test_it_reports_missing_processors(): void
    {
        $device = Device::factory()->create();
        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));

        $this->expectException(NoGraphData::class);
        $this->expectExceptionMessage('No Data');

        app(GraphAccess::class)->trusted(fn () => $graph->getDebugCommand());
    }

    public function test_it_authorizes_based_on_device(): void
    {
        $device = Device::factory()->create();

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));

        $this->actingAs(User::factory()->create());

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')
            ->with('view', Mockery::on(fn ($d) => $d instanceof Device && $d->device_id === $device->device_id))
            ->once()
            ->andReturn(true);

        $this->assertTrue($graph->authorize());
    }

    public function test_it_allows_trusted_guests(): void
    {
        $device = Device::factory()->create();

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')->never();

        $this->assertTrue(app(GraphAccess::class)->trusted(fn () => $graph->authorize()));
    }

    public function test_it_refuses_guests_without_anonymous_graphs(): void
    {
        $device = Device::factory()->create();

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')->never();

        $this->assertFalse($graph->authorize());
    }

    public function test_stacked_processors_are_drawn_as_their_share_of_the_total(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->count(2)->sequence(['processor_index' => 0], ['processor_index' => 1])->create(['device_id' => $device->device_id, 'processor_type' => 'hr']);
        LibrenmsConfig::shouldReceive('getOsSetting')->andReturn(true);

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor', 'device' => $device->device_id]));

        foreach ($graph->getSeries() as $series) {
            $this->assertTrue($series->style->stacked);
            $this->assertSame(0.5, $series->style->drawScale);
            $this->assertSame(1.0, $series->scale);
        }
    }
}
