<?php

namespace App\Graphs\Application;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;

class PostfixRejectsGraph extends ApplicationGraph
{
    public function getSeries(): array
    {
        $metric = $this->metric();

        return [
            'chr' => new DataSeries($metric, 'chr', __('graph.application.postfix.clnt_hst_rej')),
            'hcrnfqh' => new DataSeries($metric, 'hcrnfqh', __('graph.application.postfix.helo_needs_fqn')),
            'sardnf' => new DataSeries($metric, 'sardnf', __('graph.application.postfix.sndr_dmn_not_fnd')),
            'sarnobu' => new DataSeries($metric, 'sarnobu', __('graph.application.postfix.not_owned_by_user')),
            'bu' => new DataSeries($metric, 'bu', __('graph.application.postfix.rbl')),
            'raruu' => new DataSeries($metric, 'raruu', __('graph.application.postfix.unknown_recpt')),
            'hcrin' => new DataSeries($metric, 'hcrin', __('graph.application.postfix.inv_helo_name')),
            'sarnfqa' => new DataSeries($metric, 'sarnfqa', __('graph.application.postfix.sender_needs_fqa')),
            'rardnf' => new DataSeries($metric, 'rardnf', __('graph.application.postfix.recp_dnf')),
            'rarnfqa' => new DataSeries($metric, 'rarnfqa', __('graph.application.postfix.recp_needs_fqa')),
            'iuscp' => new DataSeries($metric, 'iuscp', __('graph.application.postfix.imp_pipelining')),
            'sce' => new DataSeries($metric, 'sce', __('graph.application.postfix.config_error')),
            'scp' => new DataSeries($metric, 'scp', __('graph.application.postfix.config_problem')),
            'urr' => new DataSeries($metric, 'urr', __('graph.application.postfix.unknown')),
        ];
    }

    protected function options(): GraphOptions
    {
        return new GraphOptions(legendTitle: __('graph.application.postfix.rejects'), palette: 'mixed');
    }
}
