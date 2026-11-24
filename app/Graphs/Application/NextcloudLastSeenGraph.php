<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NextcloudLastSeenGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'nextcloud_user' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $user = $this->params->input->string('nextcloud_user');
        $stat = $user === null ? 'last_seen' : "users___{$user}___last_seen";

        // stored in seconds, drawn in days
        return [
            'last_seen' => new DataSeries($this->metric(['stat' => $stat]), 'data', __('graph.application.nextcloud.last_seen'), scale: 1 / 86400,
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.nextcloud.days_ago'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
