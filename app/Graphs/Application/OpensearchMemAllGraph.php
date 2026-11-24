<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class OpensearchMemAllGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        return [
            'tqc_size' => new DataSeries($this->metric(['stat' => 'tqc']), 'tqc_size', __('graph.application.opensearch.query_cache')),
            'trc_size' => new DataSeries($this->metric(['stat' => 'trc']), 'trc_size', __('graph.application.opensearch.request_cache')),
            'tfd_size' => new DataSeries($this->metric(['stat' => 'tfd']), 'tfd_size', __('graph.application.opensearch.fielddata')),
            'tseg_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_size', __('graph.application.opensearch.segments')),
            'tseg_terms_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_terms_size', __('graph.application.opensearch.seg_terms')),
            'tseg_fields_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_fields_size', __('graph.application.opensearch.seg_fields')),
            'tseg_tvector_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_tvector_size', __('graph.application.opensearch.seg_term_vectors')),
            'tseg_norms_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_norms_size', __('graph.application.opensearch.seg_norms')),
            'tseg_points_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_points_size', __('graph.application.opensearch.seg_points')),
            'tseg_docval_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_docval_size', __('graph.application.opensearch.seg_doc_vals')),
            'tseg_indwrt_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_indwrt_size', __('graph.application.opensearch.seg_ind_wrt')),
            'tseg_vermap_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_vermap_size', __('graph.application.opensearch.seg_ver_map')),
            'tseg_fbs_size' => new DataSeries($this->metric(['stat' => 'tseg']), 'tseg_fbs_size', __('graph.application.opensearch.seg_fixed_bit_set')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.bytes'), palette: 'psychedelic');
    }
}
