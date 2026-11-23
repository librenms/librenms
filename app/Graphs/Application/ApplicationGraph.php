<?php

namespace App\Graphs\Application;

use App\Data\Graphing\GraphPageContext;
use App\Data\TimeSeries\MetricIdentity;
use App\Graphs\AbstractSeriesGraph;
use App\Models\Application;
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

        return $application !== null && $this->canView($application);
    }

    protected function validation(): array
    {
        return ['id' => 'required|integer'];
    }

    public function getPageContext(): GraphPageContext
    {
        $application = $this->findApplication();

        return new GraphPageContext($application?->device, subtitle: $application ? __('graph.application.subtitle', ['name' => $application->displayName()]) : null);
    }

    /**
     * A metric written by the application poller. Without tags it is the app-<app_type>-<app_id>.rrd file,
     * each tag value is appended to the name in order, so ['pool' => 'tank'] reads app-<app_type>-<app_id>-tank.rrd.
     *
     * @param  array<string, scalar>  $tags  the tags that tell this metric apart from the other metrics of the application
     *
     * @throws NoGraphData
     */
    protected function metric(array $tags = []): MetricIdentity
    {
        $metric = $this->application()->metricIdentity();

        return $tags === [] ? $metric : new MetricIdentity($metric->measurement, [...$metric->tags, ...$tags]);
    }

    /**
     * The requested application, for graphs that draw a series for each item in its data
     *
     * @throws NoGraphData
     */
    protected function application(): Application
    {
        return $this->findApplication() ?? throw new NoGraphData;
    }

    private function findApplication(): ?Application
    {
        return $this->params->id === null ? null : ($this->application ??= Application::with('device')->find($this->params->id));
    }
}
