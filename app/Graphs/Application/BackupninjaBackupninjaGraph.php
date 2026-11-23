<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class BackupninjaBackupninjaGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['category' => 'overview']);

        return [
            'last_actions' => new DataSeries($metric, 'last_actions', __('graph.application.backupninja.last_actions')),
            'last_fatal' => new DataSeries($metric, 'last_fatal', __('graph.application.backupninja.last_fatal')),
            'last_error' => new DataSeries($metric, 'last_error', __('graph.application.backupninja.last_error')),
            'last_warning' => new DataSeries($metric, 'last_warning', __('graph.application.backupninja.last_warning')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.backupninja.backups'), palette: 'mixed');
    }
}
