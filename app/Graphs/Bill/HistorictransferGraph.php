<?php

namespace App\Graphs\Bill;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use Amenadiel\JpGraph\Plot\LinePlot;
use LibreNMS\Billing;
use LibreNMS\Exceptions\NoGraphData;

/**
 * Transfer per day or hour for a billing period or time range, or per period for the whole bill history without imgtype
 */
class HistorictransferGraph extends BillGraph
{
    protected function validation(): array
    {
        return [
            ...parent::validation(),
            'imgtype' => 'nullable|in:day,hour',
        ];
    }

    protected function graph(): Graph
    {
        $bill_id = $this->bill()->bill_id;
        $imgtype = $this->params->input->string('imgtype');

        $graph_data = $imgtype === null
            ? Billing::getHistoricTransferGraphData($bill_id)
            : Billing::getBandwidthGraphData($bill_id, $this->histId(), $this->params->from, $this->params->to, $imgtype);

        if (empty($graph_data['ticklabels'])) {
            throw new NoGraphData;
        }

        if ($imgtype === 'day') {
            $graph_data['ticklabels'] = array_map(fn ($label) => $label ? date("j\nM", strtotime((string) $label)) : $label, $graph_data['ticklabels']);
        }

        $graph = $this->transferGraph($graph_data);

        $lineplot_allow = new LinePlot($graph_data['ave_data']);
        $lineplot_allow->SetLegend('Average');
        $lineplot_allow->SetColor('black');
        $lineplot_allow->SetWeight(1);

        $graph->Add(new GroupBarPlot($this->trafficBars($graph_data)));
        $graph->Add($lineplot_allow);

        return $graph;
    }
}
