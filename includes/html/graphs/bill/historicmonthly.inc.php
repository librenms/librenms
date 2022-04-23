<?php

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use Amenadiel\JpGraph\Plot\LinePlot;
use LibreNMS\Util\Number;

$graph_data = getHistoricTransferGraphData($vars['id']);

// Reformat date labels
for ($i = 0; $i < count($graph_data['ticklabels']); $i++) {
    if ($graph_data['ticklabels'][$i]) {
        $parts = explode(' - ', $graph_data['ticklabels'][$i]);
        $start = strtotime($parts[0]);
        $end = strtotime($parts[1]);

        if (date('m', $start) == date('m', $end) && date('d', $start == 1)) {
            // Calendar months, omit the date and the end!
            $graph_data['ticklabels'][$i] = strftime('%b %Y', $start);
        } else {
            $graph_data['ticklabels'][$i] = strftime('%e %b %Y', $start) . "\n" . strftime('%e %b %Y', $end);
        }
    }
}

// Create the graph. These two calls are always required
$graph = new Graph($vars['width'], $vars['height'], $graph_data['graph_name']);
$graph->img->SetImgFormat('png');

// work around bug in jpgraph error handling
$graph->title->Set(' ');
$graph->subtitle->Set(' ');
$graph->subsubtitle->Set(' ');
$graph->footer->left->Set(' ');
$graph->footer->center->Set(' ');
$graph->footer->right->Set(' ');

$graph->SetScale('textlin');
// $graph->title->Set("$graph_name");
$graph->title->SetFont(FF_FONT2, FS_BOLD, 10);
$graph->SetMarginColor('white');
$graph->SetFrame(false);
$graph->SetMargin('75', '30', '30', '65');
$graph->legend->SetFont(FF_FONT1, FS_NORMAL);
$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos('0.52', '0.91', 'center');

$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->SetPos('min');
$graph->xaxis->SetTitleMargin(30);
$graph->xaxis->title->Set(' ');
$graph->xaxis->SetTickLabels($graph_data['ticklabels']);

$graph->xgrid->Show(true, true);
$graph->xgrid->SetColor('#e0e0e0', '#efefef');

function YCallback($value)
{
    return Number::formatBase($value, \LibreNMS\Config::get('billing.base'), 2, 1);
}

$graph->yaxis->SetFont(FF_FONT1);
$graph->yaxis->SetTitleMargin(50);
$graph->yaxis->title->SetFont(FF_FONT1, FS_NORMAL, 10);
$graph->yaxis->title->Set('Bytes Transferred');
$graph->yaxis->SetLabelFormatCallback('YCallback');

$graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#FFFFFF@0.5');

// Create the bar plots
$barplot_tot = new BarPlot($graph_data['tot_data']);
$barplot_tot->SetLegend('Traffic total');
$barplot_tot->SetColor('darkgray');
$barplot_tot->SetFillColor('lightgray@0.4');
$barplot_tot->value->Show();
$barplot_tot->value->SetFormatCallback('format_bytes_billing_short');

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

$gbplot = new GroupBarPlot([$barplot_in, $barplot_tot, $barplot_out, $barplot_over]);

$graph->Add($gbplot);
$graph->Add($lineplot_allow);

// Display the graph
$graph->Stroke();
