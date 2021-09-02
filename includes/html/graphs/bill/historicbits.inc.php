<?php

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;

$bill_hist_id = $vars['bill_hist_id'];
$reducefactor = $vars['reducefactor'];

if (is_numeric($bill_hist_id)) {
    if ($reducefactor < 2) {
        $extents = dbFetchRow('SELECT UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) AS `to`FROM bill_history WHERE bill_id = ? AND bill_hist_id = ?', [$bill_id, $bill_hist_id]);
        $dur = $extents['to'] - $extents['from'];
        $reducefactor = round(($dur / 300 / (($vars['height'] - 100) * 3)), 0);

        if ($reducefactor < 2) {
            $reducefactor = 2;
        }
    }
    $graph_data = getBillingHistoryBitsGraphData($bill_id, $bill_hist_id, $reducefactor);
} else {
    if ($reducefactor < 2) {
        $dur = $vars['to'] - $vars['from'];
        $reducefactor = round(($dur / 300 / (($vars['height'] - 100) * 3)), 0);

        if ($reducefactor < 2) {
            $reducefactor = 2;
        }
    }
    $graph_data = getBillingBitsGraphData($bill_id, $vars['from'], $vars['to'], $reducefactor);
}

// header('Content-Type: application/json');
// print_r(json_encode($graph_data));
// exit();

$n = count($graph_data['ticks']);
$xmin = $graph_data['ticks'][0];
$xmax = $graph_data['ticks'][($n - 1)];

function TimeCallback($aVal)
{
    global $dur;

    if ($dur < 172800) {
        return date('H:i', $aVal);
    } elseif ($dur < 604800) {
        return date('D', $aVal);
    } else {
        return date('j M', $aVal);
    }
}//end TimeCallback()

function InvertCallback($x)
{
    return $x * -1;
}//end InvertCallback

function YCallback($y)
{
    return \LibreNMS\Util\Number::formatSi($y, 2, 0, '');
}

$graph = new Graph($vars['width'], $vars['height'], $graph_data['graph_name']);
$graph->img->SetImgFormat('png');

// work around bug in jpgraph error handling
$graph->title->Set(' ');
$graph->subtitle->Set(' ');
$graph->subsubtitle->Set(' ');
$graph->footer->left->Set(' ');
$graph->footer->center->Set(' ');
$graph->footer->right->Set(' ');

$graph->SetScale('datlin', 0, 0, $graph_data['from'], $graph_data['to']);
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
$graph->xaxis->SetTextLabelInterval(2);
$graph->xaxis->SetLabelFormatCallback('TimeCallBack');

$graph->yaxis->SetFont(FF_FONT1);
$graph->yaxis->SetTitleMargin(50);
$graph->yaxis->SetLabelFormatCallback('YCallback');
$graph->yaxis->HideZeroLabel(1);
$graph->yaxis->title->SetFont(FF_FONT1, FS_NORMAL, 10);
$graph->yaxis->title->Set('Bits per second');

$graph->xgrid->Show(true, true);
$graph->xgrid->SetColor('#e0e0e0', '#efefef');
$graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#FFFFFF@0.5');

// Graph Series
$lineplot = new LinePlot($graph_data['tot_data'], $graph_data['ticks']);
$lineplot->SetLegend('Traffic total');
$lineplot->SetColor('#d5d5d5');
$lineplot->SetFillColor('#d5d5d5@0.5');

$lineplot_in = new LinePlot($graph_data['in_data'], $graph_data['ticks']);
$lineplot_in->SetLegend('Traffic In');
$lineplot_in->SetColor('darkgreen');
$lineplot_in->SetFillColor('lightgreen@0.4');
$lineplot_in->SetWeight(1);

$lineplot_out = new LinePlot(array_map('InvertCallback', $graph_data['out_data']), $graph_data['ticks']);
$lineplot_out->SetLegend('Traffic Out');
$lineplot_out->SetColor('darkblue');
$lineplot_out->SetFillColor('lightblue@0.4');
$lineplot_out->SetWeight(1);

if (strtolower($graph_data['bill_type']) == 'cdr') {
    $lineplot_95th = new LinePlot([$graph_data['rate_95th'], $graph_data['rate_95th']], [$xmin, $xmax]);
    $lineplot_95th->SetColor('red');
} elseif (strtolower($graph_data['bill_type']) == 'quota') {
    $lineplot_ave = new LinePlot([$graph_data['rate_average'], $graph_data['rate_average']], [$xmin, $xmax]);
    $lineplot_ave->SetColor('red');
}

$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.52, 0.90, 'center');

$graph->Add($lineplot);
$graph->Add($lineplot_in);
$graph->Add($lineplot_out);

if (strtolower($graph_data['bill_type']) == 'cdr') {
    $graph->Add($lineplot_95th);
} elseif (strtolower($graph_data['bill_type']) == 'quota') {
    $graph->Add($lineplot_ave);
}

$graph->stroke();
