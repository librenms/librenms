<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PrivoxyVerGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'ver_1_0' => new DataSeries($metric, 'ver_1_0', __('graph.application.privoxy.http_1_0')),
            'ver_1_1' => new DataSeries($metric, 'ver_1_1', __('graph.application.privoxy.http_1_1')),
            'ver_2' => new DataSeries($metric, 'ver_2', __('graph.application.privoxy.http_2')),
            'ver_3' => new DataSeries($metric, 'ver_3', __('graph.application.privoxy.http_3')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.privoxy.http_versions'), palette: 'rainbow');
    }
}
