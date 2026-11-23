<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchTimeAllGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'ti_time' => new DataSeries($this->metric(['stat' => 'ti']), 'ti_time', __('graph.application.opensearch.index')),
            'ti_del_time' => new DataSeries($this->metric(['stat' => 'ti']), 'ti_del_time', __('graph.application.opensearch.delete')),
            'ts_q_time' => new DataSeries($this->metric(['stat' => 'ts']), 'ts_q_time', __('graph.application.opensearch.query')),
            'ts_f_time' => new DataSeries($this->metric(['stat' => 'ts']), 'ts_f_time', __('graph.application.opensearch.fetch')),
            'ts_sc_time' => new DataSeries($this->metric(['stat' => 'ts']), 'ts_sc_time', __('graph.application.opensearch.scroll')),
            'ts_su_time' => new DataSeries($this->metric(['stat' => 'ts']), 'ts_su_time', __('graph.application.opensearch.suggest')),
            'tr_time' => new DataSeries($this->metric(['stat' => 'tr']), 'tr_time', __('graph.application.opensearch.refresh')),
            'tr_ext_time' => new DataSeries($this->metric(['stat' => 'tr']), 'tr_ext_time', __('graph.application.opensearch.ext_refresh')),
            'tf_time' => new DataSeries($this->metric(['stat' => 'tf']), 'tf_time', __('graph.application.opensearch.flush')),
            'tg_time' => new DataSeries($this->metric(['stat' => 'tg']), 'tg_time', __('graph.application.opensearch.get')),
            'tg_exists_time' => new DataSeries($this->metric(['stat' => 'tg']), 'tg_exists_time', __('graph.application.opensearch.get_exists')),
            'tg_missing_time' => new DataSeries($this->metric(['stat' => 'tg']), 'tg_missing_time', __('graph.application.opensearch.get_missing')),
            'tm_time' => new DataSeries($this->metric(['stat' => 'tm']), 'tm_time', __('graph.application.opensearch.merges')),
            'tw_time' => new DataSeries($this->metric(['stat' => 'tw']), 'tw_time', __('graph.application.opensearch.warmer')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.milliseconds_per_second'), palette: 'psychedelic');
    }
}
