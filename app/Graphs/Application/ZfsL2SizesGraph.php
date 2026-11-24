<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class ZfsL2SizesGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric(['stat' => '_____group2']);

        return [
            'l2_asize' => new DataSeries($metric, 'l2_asize', __('graph.application.zfs.a_size')),
            'l2_bufc_d_asize' => new DataSeries($metric, 'l2_bufc_d_asize', __('graph.application.zfs.bufc_d_asize')),
            'l2_bufc_m_asize' => new DataSeries($metric, 'l2_bufc_m_asize', __('graph.application.zfs.bufc_m_asize')),
            'l2_log_blk_asize' => new DataSeries($metric, 'l2_log_blk_asize', __('graph.application.zfs.log_blk_asize')),
            'l2_mfu_asize' => new DataSeries($metric, 'l2_mfu_asize', __('graph.application.zfs.mfu_asize')),
            'l2_mru_asize' => new DataSeries($metric, 'l2_mru_asize', __('graph.application.zfs.mru_asize')),
            'l2_prefetch_asize' => new DataSeries($metric, 'l2_prefetch_asize', __('graph.application.zfs.prefetch_asize')),
            'l2_rb_asize' => new DataSeries($metric, 'l2_rb_asize', __('graph.application.zfs.rebuild_asize')),
            'l2_log_blk_avg_as' => new DataSeries($metric, 'l2_log_blk_avg_as', __('graph.application.zfs.log_blk_avg_asize')),
            'l2_hdr_size' => new DataSeries($metric, 'l2_hdr_size', __('graph.application.zfs.hdr_size')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
