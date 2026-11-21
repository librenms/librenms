<?php

namespace LibreNMS\Tests\Feature\Graphs\Application;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Graphs\Application\NtpClientFreqGraph;
use App\Graphs\Application\NtpClientOffsetGraph;
use App\Graphs\Application\NtpClientStabilityGraph;
use App\Graphs\Application\NtpClientStatsGraph;
use App\Graphs\GraphFactory;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class NtpClientGraphTest extends TestCase
{
    use RefreshDatabase;

    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        $device = Device::factory()->create(['hostname' => 'ntp.example.com']);
        $this->application = Application::factory()->for($device)->create(['app_type' => 'ntp-client']);
    }

    #[TestWith(['application_ntp_client_stats', NtpClientStatsGraph::class])]
    #[TestWith(['application_ntp_client_offset', NtpClientOffsetGraph::class])]
    #[TestWith(['application_ntp_client_freq', NtpClientFreqGraph::class])]
    #[TestWith(['application_ntp_client_stability', NtpClientStabilityGraph::class])]
    public function testGraphTypesResolveToClasses(string $type, string $class): void
    {
        $this->assertInstanceOf($class, app(GraphFactory::class)->graphFor($this->params($type)));
    }

    public function testStatsShowsJitterAndNoiseInSeconds(): void
    {
        $series = new NtpClientStatsGraph($this->params('application_ntp_client_stats'))->getSeries();

        $this->assertSame(['jitter', 'noise'], array_keys($series));
        $this->assertSame("ntp.example.com/app-ntp-client-{$this->application->app_id}.rrd", Rrd::metricName($series['jitter']->metric)->relativePath());
        $this->assertSame(0.001, $series['noise']->scale);
        $this->assertSame('s', $series['noise']->unit);
        $this->assertStringNotContainsString('PERCENTNAN', $this->command('application_ntp_client_stats'));
    }

    #[TestWith(['application_ntp_client_offset'])]
    #[TestWith(['application_ntp_client_freq'])]
    #[TestWith(['application_ntp_client_stability'])]
    public function testSingleSeriesGraphsDrawStats(string $type): void
    {
        LibrenmsConfig::set('graph_stat_percentile_disable', false);

        $command = $this->command($type, 691200);

        $this->assertStringContainsString('LINE1.25:ds0hourly#', $command);
        $this->assertStringContainsString('LINE1.25:ds0daily#', $command);
        $this->assertStringContainsString('LINE1.25:ds0weekly#', $command);
        $this->assertStringContainsString('VDEF:ds0p1=ds0,50,PERCENTNAN', $command);
    }

    /**
     * @param  int  $range  seconds covered by the graph
     */
    private function command(string $type, int $range = 3600): string
    {
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('ntp.example.com/app-ntp-client.rrd');

        return (string) app(GraphFactory::class)->graphFor($this->params($type, ['from' => 1000000, 'to' => 1000000 + $range]))->getDebugCommand();
    }

    /**
     * @param  array<string, mixed>  $vars  extra graph parameters, such as from and to
     */
    private function params(string $type, array $vars = []): GraphParameters
    {
        return new GraphParameters(['type' => $type, 'id' => $this->application->app_id, ...$vars]);
    }
}
