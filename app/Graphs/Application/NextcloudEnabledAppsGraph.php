<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NextcloudEnabledAppsGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'nextcloud_user' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $user = $this->params->input->string('nextcloud_user');
        $stat = $user === null ? 'enabled_apps' : "users___{$user}___enabled_apps";

        return [
            'enabled_apps' => new DataSeries($this->metric(['stat' => $stat]), 'data', __('graph.application.nextcloud.enabled_apps'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.nextcloud.apps'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
