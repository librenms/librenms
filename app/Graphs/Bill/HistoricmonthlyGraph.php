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
 * Transfer per billing period for the whole bill history
 */
class HistoricmonthlyGraph extends BillGraph
{
    protected function graph(): Graph
    {
        $graph_data = Billing::getHistoricTransferGraphData($this->bill()->bill_id);

        if (empty($graph_data['ticklabels'])) {
            throw new GraphException('No Data', 'No Data');
        }

        $graph_data['ticklabels'] = array_map(function ($label) {
            if (! $label) {
                return $label;
            }

            $parts = explode(' - ', (string) $label);
            $start = strtotime($parts[0]);
            $end = strtotime($parts[1]);

            if (date('m', $start) == date('m', $end) && date('d', $start) == 1) {
                // Calendar months, omit the date and the end!
                return date('M Y', $start);
            }

            return date('j M Y', $start) . "\n" . date('j M Y', $end);
        }, $graph_data['ticklabels']);

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

        $barplot_over = new BarPlot($graph_data['overuse_data']);
        $barplot_over->SetLegend('Traffic Overusage');
        $barplot_over->SetColor('darkred');
        $barplot_over->SetFillColor('lightred@0.4');
        $barplot_over->SetWeight(1);

        $lineplot_allow = new LinePlot($graph_data['allow_data']);
        $lineplot_allow->SetLegend('Traffic Allowed');
        $lineplot_allow->SetColor('black');
        $lineplot_allow->SetWeight(1);

        $graph->Add(new GroupBarPlot([$barplot_in, $barplot_tot, $barplot_out, $barplot_over]));
        $graph->Add($lineplot_allow);

        return $graph;
    }
}
