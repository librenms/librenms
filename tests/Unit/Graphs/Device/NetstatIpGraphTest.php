<?php

namespace LibreNMS\Tests\Unit\Graphs\Device;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\Device\NetstatIpGraph;
use App\Models\Device;
use App\Data\TimeSeries\Rrd\RrdFileValidator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use Mockery;

class NetstatIpGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturnUsing(fn ($key, $default = null) => $default);
        LibrenmsConfig::shouldReceive('get')->with('mono_font')->byDefault()->andReturn('DejaVuSansMono');
        LibrenmsConfig::shouldReceive('get')->with('display_sysname')->byDefault()->andReturn('hostname');
        LibrenmsConfig::shouldReceive('has')->byDefault()->andReturn(true);
    }

    public function test_it_returns_correct_series(): void
    {
        $device = Device::factory()->create();

        $params = new GraphParameters(['type' => 'device_netstat_ip']);
        $graph = new NetstatIpGraph($params, ['device' => $device->device_id]);

        $series = $graph->getSeries();

        $this->assertCount(7, $series);
        $this->assertArrayHasKey('ipInDelivers', $series);
        $this->assertEquals('ipInDelivers', $series['ipInDelivers']->field);
        $this->assertEquals('netstats-ip', $series['ipInDelivers']->metric->measurement);
    }

    public function test_it_inverts_out_series(): void
    {
        $device = Device::factory()->create();
        $validator = Mockery::mock(RrdFileValidator::class);
        $validator->shouldReceive('validate')->andReturn('/rrd/netstats-ip.rrd');
        $this->app->instance(RrdFileValidator::class, $validator);
        LibrenmsConfig::shouldReceive('get')->with(Mockery::pattern('/^graph_colours\./'))->andReturn('FF0000');

        $graph = new NetstatIpGraph(new GraphParameters(['type' => 'device_netstat_ip']), ['device' => $device->device_id]);
        $options = $graph->getRrdCommandOptions();

        $this->assertContains('CDEF:ds3i=ds3,-1,*', $options); // ipOutRequests
        $this->assertEmpty(preg_grep('/^CDEF:ds0i=/', $options)); // ipForwDatagrams
        $this->assertContains('-l', $options);
    }

    public function test_it_authorizes_based_on_device(): void
    {
        $device = Device::factory()->create();

        $params = new GraphParameters(['type' => 'device_netstat_ip']);
        $graph = new NetstatIpGraph($params, ['device' => $device->device_id]);

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

        $params = new GraphParameters(['type' => 'device_netstat_ip']);
        $graph = new NetstatIpGraph($params, ['device' => $device->device_id]);

        \Illuminate\Support\Facades\Gate::shouldReceive('allows')->never();

        $this->assertTrue($graph->authorize());
    }
}
