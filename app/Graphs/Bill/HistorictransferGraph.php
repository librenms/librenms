<?php

namespace App\Graphs\Bill;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use Amenadiel\JpGraph\Plot\LinePlot;
use App\Facades\LibrenmsConfig;
use LibreNMS\Billing;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Util\Number;

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
        $imgtype = $this->vars->string('imgtype');

        $graph_data = $imgtype === null
            ? Billing::getHistoricTransferGraphData($bill_id)
            : Billing::getBandwidthGraphData($bill_id, $this->histId(), $this->params->from, $this->params->to, $imgtype);

        if (empty($graph_data['ticklabels'])) {
            throw new GraphException('No Data', 'No Data');
        }

        if ($imgtype === 'day') {
            $graph_data['ticklabels'] = array_map(fn ($label) => $label ? date("j\nM", strtotime((string) $label)) : $label, $graph_data['ticklabels']);
        }

        $graph = $this->newGraph($graph_data['graph_name'], 'Bytes Transferred');
        $graph->title->SetFont(FF_FONT2, FS_BOLD, 10);
        $graph->xaxis->SetTickLabels($graph_data['ticklabels']);
        $graph->yaxis->SetLabelFormatCallback(fn ($value) => Number::formatBase($value, LibrenmsConfig::get('billing.base'), 1, 0));

        $barplot_tot = new BarPlot($graph_data['tot_data']);
        $barplot_tot->SetLegend('Traffic total');
        $barplot_tot->SetColor('darkgray');
        $barplot_tot->SetFillColor('lightgray@0.4');
        $barplot_tot->value->Show();
        $barplot_tot->value->SetFormatCallback('\LibreNMS\Billing::formatBytesShort');

        $barplot_in = new BarPlot($graph_data['in_data']);
        $barplot_in->SetLegend('Traffic In');
        $barplot_in->SetColor('darkgreen');
        $barplot_in->SetFillColor('lightgreen@0.4');
        $barplot_in->SetWeight(1);

        $barplot_out = new BarPlot($graph_data['out_data']);
        $barplot_out->SetLegend('Traffic Out');
        $barplot_out->SetColor('darkblue');
        $barplot_out->SetFillColor('lightblue@0.4');
        $barplot_out->SetWeight(1);

        $lineplot_allow = new LinePlot($graph_data['ave_data']);
        $lineplot_allow->SetLegend('Average');
        $lineplot_allow->SetColor('black');
        $lineplot_allow->SetWeight(1);

        $graph->Add(new GroupBarPlot([$barplot_in, $barplot_tot, $barplot_out]));
        $graph->Add($lineplot_allow);

        return $graph;
    }
}
