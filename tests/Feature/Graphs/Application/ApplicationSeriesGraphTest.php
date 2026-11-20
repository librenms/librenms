<?php

namespace LibreNMS\Tests\Feature\Graphs\Application;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Graphs\Application\NtpClientStatsGraph;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

/**
 * Application graphs without StatsOptions, using the ntp-client stats graph
 */
final class ApplicationSeriesGraphTest extends TestCase
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

    public function testSeriesReadTheRrdFileOfThePoller(): void
    {
        $series = $this->graph()->getSeries();

        $this->assertSame("ntp.example.com/app-ntp-client-{$this->application->app_id}.rrd", Rrd::metricName($series['jitter']->metric)->relativePath());
        $this->assertSame("ntp.example.com/app-ntp-client-{$this->application->app_id}.rrd", Rrd::metricName($series['noise']->metric)->relativePath());
    }

    public function testNoStatsLinesAreDrawn(): void
    {
        $command = $this->command(['from' => 1000000, 'to' => 1000000 + 691200]);

        $this->assertStringNotContainsString('hourly', $command);
        $this->assertStringNotContainsString('daily', $command);
        $this->assertStringNotContainsString('weekly', $command);
        $this->assertStringNotContainsString('PERCENTNAN', $command);
        $this->assertStringNotContainsString('Percentile', $command);
    }

    public function testSeveralSeriesUsePlainLabels(): void
    {
        $command = $this->command();

        // labels are padded to the default width of 16 and followed by a space
        $this->assertMatchesRegularExpression("/'LINE1\\.25:ds0#[0-9A-Fa-f]{6}:Jitter {11}'/", $command);
        $this->assertMatchesRegularExpression("/'LINE1\\.25:ds1#[0-9A-Fa-f]{6}:Noise {12}'/", $command);
    }

    public function testScaleAndUnitReachTheLegend(): void
    {
        $command = $this->command();

        $this->assertStringContainsString('CDEF:ds0=ds0raw,0.001,*', $command);
        $this->assertStringContainsString('CDEF:ds1=ds1raw,0.001,*', $command);
        $this->assertStringContainsString('GPRINT:ds0:LAST:%5.2lf%ss', $command);
        $this->assertStringContainsString('GPRINT:ds1:LAST:%5.2lf%ss', $command);
    }

    /**
     * @param  array<string, mixed>  $params  extra graph parameters, such as from and to
     */
    private function graph(array $params = []): NtpClientStatsGraph
    {
        return new NtpClientStatsGraph(new GraphParameters(['type' => 'application_ntp_client_stats', 'id' => $this->application->app_id, ...$params]));
    }

    /**
     * @param  array<string, mixed>  $params  extra graph parameters, such as from and to
     */
    private function command(array $params = []): string
    {
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('ntp.example.com/app-ntp-client.rrd');

        return (string) $this->graph($params)->getDebugCommand();
    }
}
