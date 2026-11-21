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
     * The app-<app_type>-<app_id>.rrd file written by the application poller
     *
     * @throws NoGraphData
     */
    protected function metric(): MetricIdentity
    {
        return ($this->findApplication() ?? throw new NoGraphData)->metricIdentity();
    }

    private function findApplication(): ?Application
    {
        return $this->params->id === null ? null : ($this->application ??= Application::with('device')->find($this->params->id));
    }
}
