<?php

namespace LibreNMS\Tests\Unit\Graphs\Device;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\Device\ProcessorGraph;
use App\Models\Device;
use App\Models\Processor;
use App\Models\User;
use LibreNMS\Exceptions\GraphException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $params = new GraphParameters(['type' => 'device_processor']);
        $graph = new ProcessorGraph($params, ['device' => $device->device_id]);

        $series = $graph->getSeries();

        $this->assertCount(1, $series);
        $this->assertArrayHasKey("proc_{$processor->processor_id}", $series);
        $this->assertEquals('usage', $series["proc_{$processor->processor_id}"]->field);
        $this->assertEquals('processors', $series["proc_{$processor->processor_id}"]->metric->measurement);
    }

    public function test_it_returns_a_series_per_processor(): void
    {
        $device = Device::factory()->create();
        Processor::factory()->count(2)->sequence(['processor_index' => 0], ['processor_index' => 1])->create([
            'device_id' => $device->device_id,
            'processor_type' => 'hr',
        ]);

        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor']), ['device' => $device->device_id]);
        $metrics = array_map(fn ($series) => $series->metric, array_values($graph->getSeries()));

        $this->assertCount(2, $metrics);
        $this->assertEquals(['processor', 'hr', 0], $metrics[0]->rrdName);
        $this->assertEquals(['processor_type' => 'hr', 'processor_index' => 1], $metrics[1]->tags);
    }

    public function test_it_reports_missing_processors(): void
    {
        $device = Device::factory()->create();
        $graph = new ProcessorGraph(new GraphParameters(['type' => 'device_processor']), ['device' => $device->device_id]);

        $this->expectException(GraphException::class);
        $this->expectExceptionMessage('No Processors');

        $graph->getRrdCommandOptions();
    }

    public function test_it_authorizes_based_on_device(): void
    {
        $device = Device::factory()->create();

        $params = new GraphParameters(['type' => 'device_processor']);
        $graph = new ProcessorGraph($params, ['device' => $device->device_id]);

        $this->actingAs(User::factory()->create());

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')
            ->with('view', Mockery::on(fn ($d) => $d instanceof Device && $d->device_id === $device->device_id))
            ->once()
            ->andReturn(true);

        $this->assertTrue($graph->authorize());
    }

    public function test_it_allows_guests_authenticated_by_graph_middleware(): void
    {
        $device = Device::factory()->create();

        $params = new GraphParameters(['type' => 'device_processor']);
        $graph = new ProcessorGraph($params, ['device' => $device->device_id]);

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')->never();

        $this->assertTrue($graph->authorize());
    }
}
