<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class NextcloudUsedGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'nextcloud_user' => 'nullable|string'];
    }

    public function getSeries(): array
    {
        $user = $this->params->input->string('nextcloud_user');
        $stat = $user === null ? 'used' : "users___{$user}___used";

        return [
            'used' => new DataSeries($this->metric(['stat' => $stat]), 'data', __('graph.application.nextcloud.used_storage'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.units.bytes'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
