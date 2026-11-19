<?php

namespace LibreNMS\Tests\Feature\Graphs\Application;

use App\Data\Graphing\GraphParameters;
use App\Facades\Rrd;
use App\Graphs\Application\NtpClientFreqGraph;
use App\Graphs\Application\NtpClientOffsetGraph;
use App\Graphs\Application\NtpClientStabilityGraph;
use App\Graphs\Application\NtpClientStatsGraph;
use App\Graphs\GraphAccess;
use App\Graphs\GraphFactory;
use App\Models\Application;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use Mockery;

final class NtpClientGraphTest extends TestCase
{
    use RefreshDatabase;

    public function testGraphTypesResolveToClasses(): void
    {
        $factory = app(GraphFactory::class);

        $this->assertInstanceOf(NtpClientStatsGraph::class, $factory->graphFor(new GraphParameters(['type' => 'application_ntp_client_stats', 'id' => 1])));
        $this->assertInstanceOf(NtpClientOffsetGraph::class, $factory->graphFor(new GraphParameters(['type' => 'application_ntp_client_offset', 'id' => 1])));
        $this->assertInstanceOf(NtpClientFreqGraph::class, $factory->graphFor(new GraphParameters(['type' => 'application_ntp_client_freq', 'id' => 1])));
        $this->assertInstanceOf(NtpClientStabilityGraph::class, $factory->graphFor(new GraphParameters(['type' => 'application_ntp_client_stability', 'id' => 1])));
        $this->assertContains('ntp_client_offset', $factory->subtypes('application'));
        $this->assertNotContains('ntp-client_offset', $factory->subtypes('application'));
    }

    public function testSeriesReadTheRrdFileOfThePoller(): void
    {
        $app = $this->ntpClient();

        $series = new NtpClientOffsetGraph(new GraphParameters(['type' => 'application_ntp_client_offset', 'id' => $app->app_id]))->getSeries();

        $this->assertSame('offset', $series['offset']->field);
        $this->assertSame(0.001, $series['offset']->scale);
        $this->assertSame("ntp.example.com/app-ntp-client-$app->app_id.rrd", Rrd::metricName($series['offset']->metric)->relativePath());
    }

    public function testStatsShowsJitterAndNoiseInSeconds(): void
    {
        $app = $this->ntpClient();

        $series = new NtpClientStatsGraph(new GraphParameters(['type' => 'application_ntp_client_stats', 'id' => $app->app_id]))->getSeries();

        $this->assertSame(['jitter', 'noise'], array_keys($series));
        $this->assertSame(0.001, $series['noise']->scale);
        $this->assertSame('s', $series['noise']->unit);
    }

    public function testTitleAndPageContext(): void
    {
        $app = $this->ntpClient();

        $graph = new NtpClientFreqGraph(new GraphParameters(['type' => 'application_ntp_client_freq', 'id' => $app->app_id]));

        $this->assertSame($app->device->shortDisplayName() . '::NTP Client::Freq', $graph->getTitle());
        $this->assertSame($app->device_id, $graph->getPageContext()->device?->device_id);
        $this->assertSame(' :: App :: NTP Client', $graph->getPageContext()->subtitle);
    }

    public function testMissingApplicationHasNoData(): void
    {
        $graph = new NtpClientOffsetGraph(new GraphParameters(['type' => 'application_ntp_client_offset', 'id' => 999999]));

        $this->assertFalse(app(GraphAccess::class)->trusted(fn () => $graph->authorize()));

        $this->expectException(NoGraphData::class);
        $graph->getSeries();
    }

    public function testAuthorizesBasedOnTheApplication(): void
    {
        $app = $this->ntpClient();
        $graph = new NtpClientOffsetGraph(new GraphParameters(['type' => 'application_ntp_client_offset', 'id' => $app->app_id]));

        $this->actingAs(User::factory()->create());

        Gate::shouldReceive('allows')
            ->with('view', Mockery::on(fn ($model) => $model instanceof Application && $model->app_id === $app->app_id))
            ->once()
            ->andReturn(false);

        $this->assertFalse($graph->authorize());
    }

    private function ntpClient(): Application
    {
        $device = Device::factory()->create(['hostname' => 'ntp.example.com']);

        return Application::factory()->for($device)->create(['app_type' => 'ntp-client']);
    }
}
