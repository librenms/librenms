<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Device;
use App\Models\Processor;
use App\Models\User;
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
        $response->assertJsonPath('series.0.draw_scale', 0.25);
        $response->assertJsonPath('series.0.avg', [50]);
        $response->assertJsonPath('axis.max', 100);
        $response->assertJsonPath('axis.label', 'Load %');
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
     * Every metric is kept in the given rrd file, or in none when null
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
     * An export with the same values for everything that was asked for
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
     * @return array<int, float|null> the values by the time of their row
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
