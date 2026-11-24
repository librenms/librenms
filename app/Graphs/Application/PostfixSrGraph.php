<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostfixSrGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'senders' => new DataSeries($metric, 'senders', __('graph.application.postfix.senders')),
            'sendinghd' => new DataSeries($metric, 'sendinghd', __('graph.application.postfix.sending_hd')),
            'recipients' => new DataSeries($metric, 'recipients', __('graph.application.postfix.recipients')),
            'recipienthd' => new DataSeries($metric, 'recipienthd', __('graph.application.postfix.recip_hd')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.messages'), palette: 'mixed');
    }
}
