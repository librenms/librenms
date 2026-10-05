<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\User;
use App\Services\CurrentRrdMetricService;
use App\Services\RrdSample;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use Mockery;

final class DeviceMetricsApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('api.v1.enabled', true);
    }

    /** @return array<string, string> */
    private function headersFor(User $user): array
    {
        auth()->forgetGuards();

        return ['Authorization' => 'Bearer ' . $user->createToken('test')->plainTextToken];
    }

    /** RRD reads are replaced so the tests need neither rrdtool nor RRD files. */
    private function fakeRrd(?RrdSample $sample): void
    {
        $rrd = Mockery::mock(CurrentRrdMetricService::class);
        $rrd->shouldReceive('filename')->andReturn('missing.rrd');
        $rrd->shouldReceive('latestAverage')->andReturn($sample);
        $rrd->shouldReceive('latestAverages')->andReturn([]);
        $this->app->instance(CurrentRrdMetricService::class, $rrd);
    }

    public function testEndpointsRequireABearerToken(): void
    {
        $device = Device::factory()->create();
        $this->json('GET', "/api/v1/devices/{$device->device_id}/metrics/summary")->assertStatus(401);
    }

    public function testSummaryReadsThePollerDataWithoutSnmp(): void
    {
        $this->fakeRrd(null);
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->create(['last_polled' => '2026-10-05 10:00:00']);

        $this->json('GET', "/api/v1/devices/{$device->device_id}/metrics/summary", [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('data.device_id', $device->device_id)
            ->assertJsonPath('data.hostname', $device->hostname)
            ->assertJsonPath('data.metrics.source', 'poller_database_rrd')
            ->assertJsonPath('data.metrics.disk.available', false)
            ->assertJsonPath('data.metrics.load.available', false);

        $this->json('GET', "/api/v1/devices/{$device->hostname}/metrics/summary", [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('data.device_id', $device->device_id);
    }

    public function testIoWaitIsUnavailableWithoutRecentSamples(): void
    {
        $this->fakeRrd(null);
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->create();

        $this->json('GET', "/api/v1/devices/{$device->device_id}/metrics/io-wait", [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('data.metric.name', 'io_wait')
            ->assertJsonPath('data.metric.available', false)
            ->assertJsonPath('data.metric.value', null);
    }

    public function testIoWaitIsAShareOfAllCpuStates(): void
    {
        // Every CPU state answers the same value at the same time: wait is one ninth.
        $this->fakeRrd(new RrdSample(1_760_000_000, ['value' => 90.0]));
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->create();

        $this->json('GET', "/api/v1/devices/{$device->device_id}/metrics/io-wait", [], $this->headersFor($admin))
            ->assertStatus(200)
            ->assertJsonPath('data.metric.available', true)
            ->assertJsonPath('data.metric.value', 11.1111)
            ->assertJsonPath('data.metric.unit', 'percent');
    }

    public function testUnknownDevicesAndForeignDevicesAreRefused(): void
    {
        $this->fakeRrd(null);
        /** @var User $admin */
        $admin = User::factory()->admin()->create();
        $this->json('GET', '/api/v1/devices/999999/metrics/summary', [], $this->headersFor($admin))->assertStatus(404);

        /** @var User $user */
        $user = User::factory()->create();
        $device = Device::factory()->create();
        $this->json('GET', "/api/v1/devices/{$device->device_id}/metrics/summary", [], $this->headersFor($user))->assertStatus(403);
    }
}
