<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostfixDeferralGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'deferralcr' => new DataSeries($metric, 'deferralcr', __('graph.application.postfix.conn_refused')),
            'deferralhid' => new DataSeries($metric, 'deferralhid', __('graph.application.postfix.host_is_down')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.postfix.deferrals'), palette: 'mixed');
    }
}
