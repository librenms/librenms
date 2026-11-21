<?php

namespace LibreNMS\Tests\Feature\Graphs\Application;

use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use App\Graphs\GraphFactory;
use App\Graphs\Rrd\RrdFileResolver;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * Application graphs with StatsOptions, using the ntp-client offset, freq and stability graphs
 */
final class ApplicationStatsGraphTest extends TestCase
{
    use RefreshDatabase;

    private const int DAY_AND_A_HALF = 129600;
    private const int EIGHT_DAYS = 691200;

    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('allow_unauth_graphs', true);
        LibrenmsConfig::set('graph_stat_percentile_disable', false);
        $device = Device::factory()->create(['hostname' => 'ntp.example.com']);
        $this->application = Application::factory()->for($device)->create(['app_type' => 'ntp-client']);
    }

    #[TestWith(['application_ntp_client_offset'])]
    #[TestWith(['application_ntp_client_freq'])]
    #[TestWith(['application_ntp_client_stability'])]
    public function testHourlyLinesAndPercentilesAreDrawn(string $type): void
    {
        $command = $this->command($type);

        $this->assertStringContainsString('DEF:ds0hourly', $command);
        $this->assertMatchesRegularExpression("/'LINE1\\.25:ds0hourly#[0-9A-Fa-f]{6}:1 hour avg {7}'/", $command);
        $this->assertStringContainsString("'COMMENT:  1 hour min           '", $command);
        $this->assertStringContainsString("'COMMENT:  1 hour max           '", $command);
        foreach ([0 => 25, 1 => 50, 2 => 75] as $index => $percentile) {
            $this->assertStringContainsString("VDEF:ds0p$index=ds0,$percentile,PERCENTNAN", $command);
            $this->assertMatchesRegularExpression("/'HRULE:ds0p$index#[0-9A-Fa-f]{6}:{$percentile}th Percentile  '/", $command);
        }
    }

    #[TestWith(['application_ntp_client_offset'])]
    #[TestWith(['application_ntp_client_freq'])]
    #[TestWith(['application_ntp_client_stability'])]
    public function testDailyAndWeeklyLinesFollowTheRange(string $type): void
    {
        $short = $this->command($type, self::DAY_AND_A_HALF - 1);
        $this->assertStringNotContainsString('daily', $short);
        $this->assertStringNotContainsString('weekly', $short);

        $day = $this->command($type, self::DAY_AND_A_HALF);
        $this->assertStringContainsString('LINE1.25:ds0daily#', $day);
        $this->assertStringNotContainsString('weekly', $day);

        $week = $this->command($type, self::EIGHT_DAYS);
        $this->assertStringContainsString('LINE1.25:ds0daily#', $week);
        $this->assertStringContainsString('LINE1.25:ds0weekly#', $week);
    }

    #[TestWith(['application_ntp_client_offset', 'Offset'])]
    #[TestWith(['application_ntp_client_freq', 'Frequency'])]
    #[TestWith(['application_ntp_client_stability', 'Stability'])]
    public function testSingleSeriesStatsLinesAreNotNamed(string $type, string $description): void
    {
        $command = $this->command($type, self::EIGHT_DAYS);

        $this->assertStringNotContainsString("$description 1 hour", $command);
        $this->assertStringNotContainsString("$description 1 day", $command);
        $this->assertStringNotContainsString("$description 1 week", $command);
        $this->assertStringNotContainsString("$description 25th", $command);
    }

    /**
     * @param  string  $type  the graph type, such as application_ntp_client_offset
     * @param  int  $range  seconds covered by the graph
     */
    private function command(string $type, int $range = 3600): string
    {
        $this->mock(RrdFileResolver::class)->shouldReceive('pathFor')->andReturn('ntp.example.com/app-ntp-client.rrd');
        $params = new GraphParameters(['type' => $type, 'id' => $this->application->app_id, 'from' => 1000000, 'to' => 1000000 + $range]);

        return (string) app(GraphFactory::class)->graphFor($params)->getDebugCommand();
    }
}
