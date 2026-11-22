<?php

namespace LibreNMS\Tests\Feature\Graphs\Sensor;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphFactory;
use App\Graphs\Legacy\LegacyGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Graphs\Sensor\GenericGraph;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Tests\TestCase;
use Mockery;

class GenericGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('host/sensor.rrd');
    }

    public function test_sensor_classes_without_their_own_graph_use_the_generic_graph(): void
    {
        $sensor = Sensor::factory()->for(Device::factory())->create(['sensor_class' => 'voltage']);
        $factory = new GraphFactory;

        $this->assertInstanceOf(GenericGraph::class, $factory->graphFor(new GraphParameters(['type' => 'sensor_voltage', 'id' => $sensor->sensor_id])));
        $this->assertInstanceOf(LegacyGraph::class, $factory->graphFor(new GraphParameters(['type' => 'sensor_state', 'id' => $sensor->sensor_id])));
    }

    public function test_it_identifies_the_metric_by_index_or_description(): void
    {
        $sensor = Sensor::factory()->for(Device::factory())->create(['sensor_class' => 'voltage', 'sensor_type' => 'rfc', 'sensor_index' => '7', 'sensor_descr' => 'PSU 1']);
        $hostname = $sensor->device->hostname;

        $this->assertSame(['hostname' => $hostname, 'class' => 'voltage', 'type' => 'rfc', 'index' => '7'], $sensor->metricIdentity('sensor')->tags);

        $sensor->poller_type = 'ipmi';
        $this->assertSame(['hostname' => $hostname, 'class' => 'voltage', 'type' => 'rfc', 'descr' => 'PSU 1'], $sensor->metricIdentity('sensor')->tags);
    }

    public function test_it_draws_the_range_and_thresholds(): void
    {
        $sensor = Sensor::factory()->for(Device::factory())->create([
            'sensor_class' => 'dbm', 'sensor_descr' => 'Rx Power', 'sensor_current' => -3.2,
            'sensor_limit' => 2.5, 'sensor_limit_warn' => null, 'sensor_limit_low_warn' => -12.5, 'sensor_limit_low' => -15,
        ]);

        $graph = new GenericGraph(new GraphParameters(['type' => 'sensor_dbm', 'id' => $sensor->sensor_id]));
        $command = $graph->getDebugCommand();

        $this->assertSame(' :: Sensor :: Rx Power', $graph->getPageContext()->subtitle);
        $this->assertStringContainsString('AREA:ds0range#D0D0D0::STACK', $command);
        $this->assertStringContainsString('LINE1.5:-15#00008B:low = -15 dBm:dashes', $command);
        $this->assertStringContainsString('LINE1.5:-12.5#005BDF:low_warn = -12.5 dBm:dashes', $command);
        $this->assertStringContainsString('LINE1.5:2.5#FF0000:high = 2.5 dBm\\l:dashes', $command);
        $this->assertStringNotContainsString('high_warn', $command);
        $this->assertStringContainsString("'--left-axis-format' '%lg dBm'", $command);
    }

    public function test_temperatures_follow_the_unit_the_user_prefers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        UserPref::setPref($user, 'temp_units', 'f');
        $sensor = Sensor::factory()->for(Device::factory())->create(['sensor_class' => 'temperature', 'sensor_current' => 20, 'sensor_limit' => 100, 'sensor_limit_warn' => null, 'sensor_limit_low' => null, 'sensor_limit_low_warn' => null]);
        Gate::shouldReceive('allows')->with('view', Mockery::type(Sensor::class))->andReturn(true);

        $command = new GenericGraph(new GraphParameters(['type' => 'sensor_temperature', 'id' => $sensor->sensor_id]))->getDebugCommand();

        $this->assertStringContainsString('CDEF:ds0=ds0raw,1.8,*,32,+', $command);
        $this->assertStringContainsString('GPRINT:ds0:LAST: %7.2lf%s°F\g', $command);
        $this->assertStringContainsString('LINE1.5:212#FF0000:high = 212 °F\\l:dashes', $command);
    }
}
