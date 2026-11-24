<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class Fail2banJailGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'jail' => 'required|string'];
    }

    public function getSeries(): array
    {
        return [
            'banned' => new DataSeries($this->metric(['jail' => $this->params->input->string('jail')]), 'banned', __('graph.application.fail2ban.banned'),
                style: new SeriesStyle(fill: true, rollingAverages: true, percentileLines: true)),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(
            legendTitle: __('graph.application.fail2ban.banned_ips'),
            palette: 'stats',
            percentiles: GraphOptions::statsPercentiles(),
        );
    }
}
