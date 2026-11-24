<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class NextcloudUserStorageGraph extends ApplicationGraph
{
    protected function validation(): array
    {
        return [...parent::validation(), 'nextcloud_user' => 'required|string'];
    }

    public function getSeries(): array
    {
        $user = $this->params->input->string('nextcloud_user');

        return [
            'free' => new DataSeries($this->metric(['stat' => "users___{$user}___free"]), 'data', __('graph.series.free')),
            'used' => new DataSeries($this->metric(['stat' => "users___{$user}___used"]), 'data', __('graph.series.used')),
            'total' => new DataSeries($this->metric(['stat' => "users___{$user}___total"]), 'data', __('graph.series.total')),
            'quota' => new DataSeries($this->metric(['stat' => "users___{$user}___quota"]), 'data', __('graph.application.nextcloud.quota')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
