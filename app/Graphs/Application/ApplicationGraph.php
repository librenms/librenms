<?php

namespace App\Graphs\Application;

use App\Data\Graphing\GraphPageContext;
use App\Data\TimeSeries\MetricIdentity;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Application;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\NoGraphData;

/**
 * A graph of one application, viewable by users who can view the application
 */
abstract class ApplicationGraph extends AbstractSeriesGraph
{
    private ?Application $application = null;

    protected function isAuthorized(): bool
    {
        $application = $this->findApplication();

        return $application !== null && $application->device !== null && $this->canView($application);
    }

    protected function validation(): array
    {
        return ['id' => 'required|integer'];
    }

    public function getPageContext(): GraphPageContext
    {
        $application = $this->findApplication();

        return new GraphPageContext($application?->device, subtitle: $application ? ' :: App :: ' . $application->displayName() : null);
    }

    /**
     * The device, the application and the graph, such as server1::NTP Client::Offset.
     * The graph is named after the subtype without the application, ntp_client_offset is Offset.
     */
    public function getTitle(): string
    {
        $application = $this->application();
        $graph = Str::after($this->params->subtype, str_replace('-', '_', $application->app_type) . '_');

        return $application->device?->shortDisplayName() . '::' . $application->displayName() . '::' . Str::title(str_replace('_', ' ', $graph));
    }

    /**
     * @throws NoGraphData
     */
    protected function application(): Application
    {
        return $this->findApplication() ?? throw new NoGraphData;
    }

    /**
     * @param  array<string, scalar>  $tags  tags after the app id, in the order the rrd file name holds them, see Application::metricIdentity()
     *
     * @throws NoGraphData
     */
    protected function metric(array $tags = []): MetricIdentity
    {
        return $this->application()->metricIdentity($tags);
    }

    private function findApplication(): ?Application
    {
        return $this->params->id === null ? null : ($this->application ??= Application::with('device')->find($this->params->id));
    }
}
