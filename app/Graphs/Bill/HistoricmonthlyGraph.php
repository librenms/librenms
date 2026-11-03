<?php

namespace App\Graphs\Bill;

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use Amenadiel\JpGraph\Plot\LinePlot;
use LibreNMS\Billing;
use LibreNMS\Exceptions\NoGraphData;

/**
 * Transfer per billing period for the whole bill history
 */
class HistoricmonthlyGraph extends BillGraph
{
    protected function graph(): Graph
    {
        $graph_data = Billing::getHistoricTransferGraphData($this->bill()->bill_id);

        if (empty($graph_data['ticklabels'])) {
            throw new NoGraphData;
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

        $graph = $this->transferGraph($graph_data);

        $barplot_over = new BarPlot($graph_data['overuse_data']);
        $barplot_over->SetLegend('Traffic Overusage');
        $barplot_over->SetColor('darkred');
        $barplot_over->SetFillColor('lightred@0.4');
        $barplot_over->SetWeight(1);

        $lineplot_allow = new LinePlot($graph_data['allow_data']);
        $lineplot_allow->SetLegend('Traffic Allowed');
        $lineplot_allow->SetColor('black');
        $lineplot_allow->SetWeight(1);

        $graph->Add(new GroupBarPlot([...$this->trafficBars($graph_data), $barplot_over]));
        $graph->Add($lineplot_allow);

        return $graph;
    }
}
