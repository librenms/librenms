<?php

namespace LibreNMS\Tests\Feature\Graphs\Application;

use App\Data\Graphing\GraphParameters;
use App\Graphs\Application\ApplicationGraph;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * Titles and page context of application graphs, ApplicationGraph::getTitle() and getPageContext()
 */
final class ApplicationGraphTitleTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['ntp-client', 'application_ntp_client_freq', 'NTP Client::Freq'])]
    #[TestWith(['ntp-client', 'application_ntp_client_foo_bar', 'NTP Client::Foo Bar'])]
    #[TestWith(['example', 'application_example_offset', 'Example::Offset'])]
    #[TestWith(['example', 'application_example_foo_bar', 'Example::Foo Bar'])]
    public function testTitleIsTheDeviceAppAndGraph(string $appType, string $graphType, string $expected): void
    {
        $device = Device::factory()->create(['hostname' => 'app.example.com']);
        $application = Application::factory()->for($device)->create(['app_type' => $appType]);

        $this->assertSame($device->shortDisplayName() . "::$expected", $this->graph($graphType, $application->app_id)->getTitle());
    }

    public function testSubtypeWithoutTheAppPrefixIsTitledInFull(): void
    {
        $device = Device::factory()->create(['hostname' => 'app.example.com']);
        $application = Application::factory()->for($device)->create(['app_type' => 'ntp-client']);

        // Str::after() returns the whole subtype when the prefix is not found
        $this->assertSame($device->shortDisplayName() . '::NTP Client::Other Offset', $this->graph('application_other_offset', $application->app_id)->getTitle());
    }

    public function testTitleWithoutADeviceStartsWithTheApp(): void
    {
        $application = Application::factory()->create(['device_id' => 999999, 'app_type' => 'ntp-client']);

        $this->assertSame('::NTP Client::Freq', $this->graph('application_ntp_client_freq', $application->app_id)->getTitle());
    }

    public function testPageContextIsTheDeviceAndApp(): void
    {
        $device = Device::factory()->create(['hostname' => 'app.example.com']);
        $application = Application::factory()->for($device)->create(['app_type' => 'ntp-client']);

        $context = $this->graph('application_ntp_client_freq', $application->app_id)->getPageContext();

        $this->assertSame($device->device_id, $context->device?->device_id);
        $this->assertSame(' :: App :: NTP Client', $context->subtitle);
    }

    public function testPageContextOfAMissingAppIsEmpty(): void
    {
        $context = $this->graph('application_ntp_client_freq', 999999)->getPageContext();

        $this->assertNull($context->device);
        $this->assertNull($context->subtitle);
    }

    /**
     * An application graph with no series, as the title and page context do not depend on them.
     *
     * @param  string  $type  the graph type, such as application_ntp_client_freq
     * @param  int  $id  the app_id of the application
     */
    private function graph(string $type, int $id): ApplicationGraph
    {
        return new class(new GraphParameters(['type' => $type, 'id' => $id])) extends ApplicationGraph
        {
            public function getSeries(): array
            {
                return [];
            }
        };
    }
}
