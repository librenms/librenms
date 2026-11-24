<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PureftpdUsersGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => 'users']);

        return [
            'total' => new DataSeries($metric, 'total', __('graph.series.total')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.pureftpd.users_connected'), palette: 'mixed');
    }
}
