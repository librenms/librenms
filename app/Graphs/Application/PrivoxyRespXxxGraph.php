<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PrivoxyRespXxxGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'resp_1xx' => new DataSeries($metric, 'resp_1xx', __('graph.application.privoxy.http_1xx')),
            'resp_2xx' => new DataSeries($metric, 'resp_2xx', __('graph.application.privoxy.http_2xx')),
            'resp_3xx' => new DataSeries($metric, 'resp_3xx', __('graph.application.privoxy.http_3xx')),
            'resp_4xx' => new DataSeries($metric, 'resp_4xx', __('graph.application.privoxy.http_4xx')),
            'resp_5xx' => new DataSeries($metric, 'resp_5xx', __('graph.application.privoxy.http_5xx')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.privoxy.response_types'), palette: 'rainbow');
    }
}
