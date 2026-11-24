<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PrivoxyReqGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'req_connect' => new DataSeries($metric, 'req_connect', __('graph.application.privoxy.connect')),
            'req_delete' => new DataSeries($metric, 'req_delete', __('graph.application.privoxy.delete')),
            'req_get' => new DataSeries($metric, 'req_get', __('graph.application.privoxy.get')),
            'req_head' => new DataSeries($metric, 'req_head', __('graph.application.privoxy.head')),
            'req_options' => new DataSeries($metric, 'req_options', __('graph.application.privoxy.options')),
            'req_patch' => new DataSeries($metric, 'req_patch', __('graph.application.privoxy.patch')),
            'req_post' => new DataSeries($metric, 'req_post', __('graph.application.privoxy.post')),
            'req_put' => new DataSeries($metric, 'req_put', __('graph.application.privoxy.put')),
            'req_trace' => new DataSeries($metric, 'req_trace', __('graph.application.privoxy.trace')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.privoxy.request_types'), palette: 'rainbow');
    }
}
