<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\User;
use App\Models\UserPref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Exceptions\RrdUnknownException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\Tests\TestCase;
use Mockery;
use Spatie\Permission\Models\Role;

class GraphDataControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        LibrenmsConfig::set('auth_mechanism', 'mysql');
    }

    public function testReturnsTheSeriesOfAGraph(): void
    {
        $device = Device::factory()->create();
        $backend = $this->fakeRrdFiles('localhost/netstats-ip.rrd');
        $backend->shouldReceive('xport')->once()
            ->with(Mockery::on(fn (array $options) => array_slice($options, 0, 6) === ['--start', '1700000000', '--end', '1700003600', '--maxrows', '800']))
            ->andReturnUsing(fn (array $options) => $this->export($options, [1.5, null]));

        $response = $this->actingAs($this->adminUser())
            ->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id&from=1700000000&to=1700003600&width=800");

        $response->assertOk();
        $response->assertJsonPath('timestamps', [1700000300, 1700000600]);
        $response->assertJsonPath('axis.min', 0);
        $response->assertJsonPath('axis.base', 1000);
        $response->assertJsonCount(7, 'series');
        $response->assertJsonPath('series.0.key', 'ipForwDatagrams');
        $response->assertJsonPath('series.0.label', 'Fwd Datagrams');
        $response->assertJsonPath('series.0.color', '#' . LibrenmsConfig::get('graph_colours.mixed.0'));
        $response->assertJsonPath('series.0.invert', false);
        $response->assertJsonPath('series.0.avg', [1.5, null]);
        $response->assertJsonPath('series.0.min', null);
        $response->assertJsonPath('series.0.max', null);
        $response->assertJsonPath('series.3.key', 'ipOutRequests');
        $response->assertJsonPath('series.3.invert', true);
    }

    public function testMinAndMaxAreOnlySentWhenTheyDifferFromTheAverage(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdFiles('localhost/netstats-ip.rrd')->shouldReceive('xport')->andReturnUsing(fn (array $options) => ['ds0min' => $this->timed([1.0, null]), 'ds1max' => $this->timed([3.0, null])] + $this->export($options, [2.0, null]));

        $response = $this->actingAs($this->adminUser())->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id");

        $response->assertOk();
        $response->assertJsonPath('series.0.avg', [2, null]);
        $response->assertJsonPath('series.0.min', [1, null]);
        $response->assertJsonPath('series.0.max', null);
        $response->assertJsonPath('series.1.min', null);
        $response->assertJsonPath('series.1.max', [3, null]);
    }

    public function testStackedSeriesAreDrawnAsTheirShare(): void
    {
        $device = Device::factory()->create(['os' => 'linux']);
        LibrenmsConfig::set('os.linux.processor_stacked', true);
        Processor::factory()->count(4)->create(['device_id' => $device->device_id]);
        $this->fakeRrdFiles('localhost/processor.rrd')->shouldReceive('xport')
            ->andReturnUsing(fn (array $options) => $this->export($options, [50.0]));

        $response = $this->actingAs($this->adminUser())->getJson("/graph-data?type=device_processor&device=$device->device_id");

        $response->assertOk();
        $response->assertJsonCount(4, 'series');
        $response->assertJsonPath('series.0.stacked', true);
        $response->assertJsonPath('series.0.drawScale', 0.25);
        $response->assertJsonPath('series.0.avg', [50]);
        $response->assertJsonPath('axis.max', 100);
        $response->assertJsonPath('axis.label', 'Load %');
    }

    public function testThresholdsThemeColorsAndConvertedValuesAreSent(): void
    {
        $sensor = Sensor::factory()->for(Device::factory())->create(['sensor_class' => 'temperature', 'sensor_limit' => 80]);
        UserPref::setPref($user = $this->adminUser(), 'temp_units', 'f');
        $this->fakeRrdFiles('localhost/sensor.rrd')->shouldReceive('xport')
            ->with(Mockery::on(fn (array $options) => in_array('CDEF:ds0avg=ds0avgraw,1.8,*,32,+', $options, true)))
            ->andReturnUsing(fn (array $options) => $this->export($options, [212.0]));

        $response = $this->actingAs($user)->withSession(['applied_site_style' => 'dark'])->getJson("/graph-data?type=sensor_temperature&id=$sensor->sensor_id");

        $response->assertOk();
        $response->assertJsonPath('series.0.avg', [212]);
        $response->assertJsonPath('series.0.color', '#F2F2F2');
        $response->assertJsonPath('series.0.rangeColor', '#3E444C');
        $response->assertJsonPath('axis.margin', 0.01);
        $response->assertJsonFragment(['value' => 176, 'color' => '#FF0000', 'shown' => true]); // in the unit of the series
    }

    public function testGraphsWithoutDataFilesHaveNoData(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdFiles(null)->shouldNotReceive('xport');

        $response = $this->actingAs($this->adminUser())->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id");

        $response->assertStatus(500);
        $response->assertJsonPath('message', 'No Data files');
    }

    public function testDatastoreErrorsAreNotExposed(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdFiles('localhost/netstats-ip.rrd')->shouldReceive('xport')
            ->andThrow(new RrdUnknownException('ERROR: opening /secret/path.rrd'));

        $response = $this->actingAs($this->adminUser())->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id");

        $response->assertStatus(500);
        $response->assertJsonPath('message', 'Graph Error');
        $response->assertDontSee('/secret/path.rrd');
    }

    public function testRequestedAxisLimitsApplyWhenTheGraphSetsNone(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdFiles('localhost/netstats-ip.rrd')->shouldReceive('xport')
            ->andReturnUsing(fn (array $options) => $this->export($options, [1.0]));

        $response = $this->actingAs($this->adminUser())->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id&scale_min=-5&scale_max=50");

        $response->assertOk();
        $response->assertJsonPath('axis.min', 0); // set by the graph
        $response->assertJsonPath('axis.max', 50);
    }

    public function testGraphsDrawnByScriptsHaveNoData(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->adminUser())
            ->getJson("/graph-data?type=device_poller_perf&device=$device->device_id")
            ->assertNotFound();
    }

    public function testUsersNeedAccessToTheDevice(): void
    {
        $device = Device::factory()->create();

        $this->actingAs(User::factory()->create(['enabled' => 1]))
            ->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id")
            ->assertForbidden();
    }

    public function testGuestsAreNotServed(): void
    {
        $device = Device::factory()->create();
        LibrenmsConfig::set('allow_unauth_graphs', true);

        $this->getJson("/graph-data?type=device_netstat_ip&device=$device->device_id")->assertUnauthorized();
    }

    /**
     * Every metric is in the given rrd file, null for none
     */
    private function fakeRrdFiles(?string $file): RrdBackendInterface&Mockery\MockInterface
    {
        $rrdFiles = Mockery::mock(RrdFileResolver::class);
        $rrdFiles->shouldReceive('pathFor')->andReturn($file);
        $this->app->instance(RrdFileResolver::class, $rrdFiles);

        $backend = Mockery::mock(RrdBackendInterface::class);
        $this->app->instance(RrdBackendInterface::class, $backend);

        return $backend;
    }

    /**
     * The same values for every XPORT
     *
     * @param  string[]  $options
     * @param  list<float|null>  $values
     * @return array<string, array<int, float|null>>
     */
    private function export(array $options, array $values): array
    {
        $columns = [];
        foreach (preg_grep('/^XPORT:/', $options) as $xport) {
            $columns[explode(':', $xport)[2]] = $this->timed($values);
        }

        return $columns;
    }

    /**
     * @param  list<float|null>  $values
     * @return array<int, float|null>
     */
    private function timed(array $values): array
    {
        return array_combine(array_slice([1700000300, 1700000600], 0, count($values)), $values);
    }

    private function adminUser(): User
    {
        return User::factory()->create(['enabled' => 1])->assignRole('admin');
    }
}
