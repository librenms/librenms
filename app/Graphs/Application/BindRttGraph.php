<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BindRttGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'resolver']);

        return [
            'rttl10' => new DataSeries($metric, 'rttl10', __('graph.application.bind.rtt_lt_10')),
            'rtt10t100' => new DataSeries($metric, 'rtt10t100', __('graph.application.bind.rtt_10_100')),
            'rtt100t500' => new DataSeries($metric, 'rtt100t500', __('graph.application.bind.rtt_100_500')),
            'rtt500t800' => new DataSeries($metric, 'rtt500t800', __('graph.application.bind.rtt_500_800')),
            'rtt800t1600' => new DataSeries($metric, 'rtt800t1600', __('graph.application.bind.rtt_800_1600')),
            'rttg1600' => new DataSeries($metric, 'rttg1600', __('graph.application.bind.rtt_gt_1600')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.bind.rtt_in_ms_sec'), palette: 'psychedelic');
    }
}
