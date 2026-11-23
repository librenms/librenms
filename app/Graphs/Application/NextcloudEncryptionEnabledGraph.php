<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class NextcloudEncryptionEnabledGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'encryption' => new DataSeries($this->metric(['stat' => 'encryption_enabled']), 'data', __('graph.application.nextcloud.encryption')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.nextcloud.enabled'), palette: 'psychedelic');
    }
}
