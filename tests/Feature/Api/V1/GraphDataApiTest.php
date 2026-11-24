<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Http\Middleware\EnforceJsonApi;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class GraphDataApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    public function testReturnsTheSeriesOfAGraph(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdFiles('localhost/netstats-ip.rrd')->shouldReceive('xport')
            ->andReturnUsing(fn (array $options) => $this->export($options, [1.5, null]));
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson("/api/v1/graph-data?type=device_netstat_ip&device=$device->device_id&from=1700000000&to=1700003600")
            ->assertOk()
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonPath('data.type', 'graph-data')
            ->assertJsonPath('data.id', 'device_netstat_ip')
            ->assertJsonPath('data.attributes.timestamps', [1700000300, 1700000600])
            ->assertJsonPath('data.attributes.series.0.key', 'ipForwDatagrams')
            ->assertJsonPath('data.attributes.series.0.avg', [1.5, null]);
    }

    public function testUsersNeedAccessToTheDevice(): void
    {
        $device = Device::factory()->create();
        Sanctum::actingAs($this->apiUser('user'));

        $this->getJson("/api/v1/graph-data?type=device_netstat_ip&device=$device->device_id")
            ->assertForbidden()
            ->assertJsonPath('errors.0.status', '403');
    }

    public function testApiAccessIsRequired(): void
    {
        $device = Device::factory()->create();
        Sanctum::actingAs(User::factory()->create(['enabled' => 1])->assignRole(Role::findOrCreate('global-read')));

        $this->getJson("/api/v1/graph-data?type=device_netstat_ip&device=$device->device_id")
            ->assertForbidden();
    }

    public function testATokenIsRequired(): void
    {
        $device = Device::factory()->create();
        LibrenmsConfig::set('allow_unauth_graphs', true);

        $this->getJson("/api/v1/graph-data?type=device_netstat_ip&device=$device->device_id")
            ->assertUnauthorized();
    }

    public function testInvalidParametersAreValidationErrors(): void
    {
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson('/api/v1/graph-data?type=device_netstat_ip&width=1')
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/width');
    }

    public function testGraphsDrawnByScriptsHaveNoData(): void
    {
        $device = Device::factory()->create();
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson("/api/v1/graph-data?type=device_poller_perf&device=$device->device_id")
            ->assertNotFound()
            ->assertJsonPath('errors.0.status', '404');
    }

    public function testGraphsWithoutDataFilesAreServerErrors(): void
    {
        $device = Device::factory()->create();
        $this->fakeRrdFiles(null)->shouldNotReceive('xport');
        Sanctum::actingAs($this->apiUser('admin'));

        $this->getJson("/api/v1/graph-data?type=device_netstat_ip&device=$device->device_id")
            ->assertStatus(500);
    }

    private function apiUser(string $role): User
    {
        $user = User::factory()->create(['enabled' => 1])->assignRole(Role::findOrCreate($role));
        $user->givePermissionTo(Permission::findOrCreate('api.access'));

        return $user;
    }

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
            $columns[explode(':', $xport)[2]] = array_combine(array_slice([1700000300, 1700000600], 0, count($values)), $values);
        }

        return $columns;
    }
}
