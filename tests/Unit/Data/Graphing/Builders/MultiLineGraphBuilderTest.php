<?php

namespace LibreNMS\Tests\Unit\Data\Graphing\Builders;

use App\Data\Graphing\Builders\MultiLineGraphBuilder;
use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\TimeSeries\Contracts\MetricValidator;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use LibreNMS\Interfaces\Data\Graphing\GraphDataInterface;
use LibreNMS\Tests\TestCase;
use Mockery;

class MultiLineGraphBuilderTest extends TestCase
{
    private $graph;

    protected function setUp(): void
    {
        parent::setUp();
        $this->graph = Mockery::mock(GraphDataInterface::class);
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturn(null);
        LibrenmsConfig::shouldReceive('get')->with('mono_font')->byDefault()->andReturn('DejaVuSansMono');
    }

    public function test_it_builds_rrd_command_from_data_series(): void
    {
        $metric = new MetricIdentity('test-metric', ['device_id' => 1]);
        $series = [
            'series1' => new DataSeries($metric, 'field1', 'Description1'),
        ];
        $this->graph->shouldReceive('getSeries')->andReturn($series);

        $validator = Mockery::mock(MetricValidator::class);
        $validator->shouldReceive('validate')->with($metric)->once()->andReturn('/opt/librenms/rrd/device/test.rrd');

        LibrenmsConfig::shouldReceive('get')->with('webui.graph_stacked')->andReturn(false);
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.mixed.0')->andReturn('FF0000');

        $builder = new MultiLineGraphBuilder($this->graph, $validator);

        $params = new GraphParameters(['from' => 12345, 'to' => 67890]);
        $definition = $builder->build($params);

        $this->assertContains('DEF:ds0=/opt/librenms/rrd/device/test.rrd:field1:AVERAGE', $definition);
        $this->assertContains('LINE1.25:ds0#FF0000:Description1 ', $definition);
    }

    public function test_it_skips_missing_rrd_files(): void
    {
        $metric = new MetricIdentity('test-metric', ['device_id' => 1]);
        $series = [
            'series1' => new DataSeries($metric, 'field1', 'Description 1'),
        ];
        $this->graph->shouldReceive('getSeries')->andReturn($series);

        $validator = Mockery::mock(MetricValidator::class);
        $validator->shouldReceive('validate')->with($metric)->once()->andReturn(null);

        $builder = new MultiLineGraphBuilder($this->graph, $validator);

        $params = new GraphParameters(['from' => 12345, 'to' => 67890]);
        $definition = $builder->build($params);

        // Should not contain DEF or LINE for series1
        foreach ($definition as $line) {
            $this->assertStringNotContainsString('series1', $line);
        }
    }
}
