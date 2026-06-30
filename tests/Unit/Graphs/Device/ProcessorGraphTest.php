<?php

namespace LibreNMS\Tests\Unit\Graphs\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Processor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Data\Graphing\GraphParameters;
use App\Graphs\Device\ProcessorGraph;
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
        $this->assertArrayHasKey("proc_{$processor->id}", $series);
        $this->assertEquals('usage', $series["proc_{$processor->id}"]->field);
        $this->assertEquals('processor', $series["proc_{$processor->id}"]->metric->name);
    }

    public function test_it_authorizes_based_on_device(): void
    {
        $device = Device::factory()->create();

        $params = new GraphParameters(['type' => 'device_processor']);
        $graph = new ProcessorGraph($params, ['device' => $device->device_id]);

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')
            ->with('view', Mockery::on(fn ($d) => $d instanceof Device && $d->device_id === $device->device_id))
            ->once()
            ->andReturn(true);

        $this->assertTrue($graph->authorize());
    }
}
