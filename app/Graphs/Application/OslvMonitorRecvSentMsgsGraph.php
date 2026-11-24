<?php

namespace App\Graphs\Application;

class OslvMonitorRecvSentMsgsGraph extends OslvMonitorGraph
{
    protected function stats(): array
    {
        return [
            'received-messages' => __('graph.series.received'),
            'sent-messages' => __('graph.series.sent'),
        ];
    }

    protected function legendTitle(): string
    {
        return __('graph.units.messages_per_second');
    }
}
