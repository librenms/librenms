<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NextcloudUserCountGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'nextcloud_user' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $user = $this->params->input->string('nextcloud_user');
        $stat = $user === null ? 'user_count' : "users___{$user}___user_count";

        return [
            'user_count' => new DataSeries($this->metric(['stat' => $stat]), 'data', __('graph.application.nextcloud.user_count'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.nextcloud.users'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
