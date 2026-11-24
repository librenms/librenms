<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class FbsdNfsClientRpcGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'timedout' => new DataSeries($metric, 'timedout', __('graph.application.fbsd_nfs_client.timed_out'), style: $style),
            'invalid' => new DataSeries($metric, 'invalid', __('graph.application.fbsd_nfs_client.invalid'), style: $style),
            'xreplies' => new DataSeries($metric, 'xreplies', __('graph.application.fbsd_nfs_client.x_replies'), style: $style),
            'retries' => new DataSeries($metric, 'retries', __('graph.application.fbsd_nfs_client.retries'), style: $style),
            'requests' => new DataSeries($metric, 'requests', __('graph.application.fbsd_nfs_client.requests'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
