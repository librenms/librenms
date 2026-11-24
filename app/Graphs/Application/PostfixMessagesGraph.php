<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostfixMessagesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'received' => new DataSeries($metric, 'received', __('graph.series.received')),
            'delivered' => new DataSeries($metric, 'delivered', __('graph.application.postfix.delivered')),
            'deferred' => new DataSeries($metric, 'deferred', __('graph.application.postfix.deferred')),
            'bounced' => new DataSeries($metric, 'bounced', __('graph.application.postfix.bounced')),
            'rejected' => new DataSeries($metric, 'rejected', __('graph.series.rejected')),
            'rejectw' => new DataSeries($metric, 'rejectw', __('graph.application.postfix.reject_warnings')),
            'held' => new DataSeries($metric, 'held', __('graph.application.postfix.held')),
            'discarded' => new DataSeries($metric, 'discarded', __('graph.application.postfix.discarded')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.messages'), palette: 'mixed');
    }
}
