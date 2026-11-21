<?php

namespace LibreNMS\Tests\Feature\Graphs\Application;

use App\Data\Graphing\GraphParameters;
use App\Graphs\Application\NtpClientFreqGraph;
use App\Graphs\Application\NtpClientOffsetGraph;
use App\Graphs\Application\NtpClientStabilityGraph;
use App\Graphs\Application\NtpClientStatsGraph;
use App\Graphs\GraphFactory;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

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

    public function testStatsShowsJitterAndNoiseInSeconds(): void
    {
        $app = $this->ntpClient();

        $series = new NtpClientStatsGraph(new GraphParameters(['type' => 'application_ntp_client_stats', 'id' => $app->app_id]))->getSeries();

        $this->assertSame(['jitter', 'noise'], array_keys($series));
        $this->assertSame(0.001, $series['noise']->scale);
        $this->assertSame('s', $series['noise']->unit);
    }

    #[TestWith(['application_ntp_client_stats', 'Stats'])]
    #[TestWith(['application_ntp_client_offset', 'Offset'])]
    #[TestWith(['application_ntp_client_freq', 'Freq'])]
    #[TestWith(['application_ntp_client_stability', 'Stability'])]
    public function testTitles(string $type, string $graph): void
    {
        $app = $this->ntpClient();

        $title = app(GraphFactory::class)->graphFor(new GraphParameters(['type' => $type, 'id' => $app->app_id]))->getTitle();

        $this->assertSame($app->device->shortDisplayName() . "::NTP Client::$graph", $title);
    }

    private function ntpClient(): Application
    {
        $device = Device::factory()->create(['hostname' => 'ntp.example.com']);

        return Application::factory()->for($device)->create(['app_type' => 'ntp-client']);
    }
}
