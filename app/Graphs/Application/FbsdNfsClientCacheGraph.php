<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\SeriesStyle;

class FbsdNfsClientCacheGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();
        $style = new SeriesStyle(fill: true);

        return [
            'attrhits' => new DataSeries($metric, 'attrhits', __('graph.application.fbsd_nfs_client.attr_hits'), style: $style),
            'attrmisses' => new DataSeries($metric, 'attrmisses', __('graph.application.fbsd_nfs_client.attr_misses'), style: $style),
            'lkuphits' => new DataSeries($metric, 'lkuphits', __('graph.application.fbsd_nfs_client.lkup_hits'), style: $style),
            'lkupmisses' => new DataSeries($metric, 'lkupmisses', __('graph.application.fbsd_nfs_client.lkup_misses'), style: $style),
            'biorhits' => new DataSeries($metric, 'biorhits', __('graph.application.fbsd_nfs_client.bior_hits'), style: $style),
            'biormisses' => new DataSeries($metric, 'biormisses', __('graph.application.fbsd_nfs_client.bior_misses'), style: $style),
            'biowhits' => new DataSeries($metric, 'biowhits', __('graph.application.fbsd_nfs_client.biow_hits'), style: $style),
            'biowmisses' => new DataSeries($metric, 'biowmisses', __('graph.application.fbsd_nfs_client.biow_misses'), style: $style),
            'biorlhits' => new DataSeries($metric, 'biorlhits', __('graph.application.fbsd_nfs_client.biorl_hits'), style: $style),
            'biorlmisses' => new DataSeries($metric, 'biorlmisses', __('graph.application.fbsd_nfs_client.biorl_misses'), style: $style),
            'biodhits' => new DataSeries($metric, 'biodhits', __('graph.application.fbsd_nfs_client.biod_hits'), style: $style),
            'biodmisses' => new DataSeries($metric, 'biodmisses', __('graph.application.fbsd_nfs_client.biod_misses'), style: $style),
            'direhits' => new DataSeries($metric, 'direhits', __('graph.application.fbsd_nfs_client.dire_hits'), style: $style),
            'diremisses' => new DataSeries($metric, 'diremisses', __('graph.application.fbsd_nfs_client.dire_misses'), style: $style),
            'accshits' => new DataSeries($metric, 'accshits', __('graph.application.fbsd_nfs_client.accs_hits'), style: $style),
            'accsmisses' => new DataSeries($metric, 'accsmisses', __('graph.application.fbsd_nfs_client.accs_misses'), style: $style),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.units.per_second'), palette: 'mixed');
    }
}
