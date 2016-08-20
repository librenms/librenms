<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage billing
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

if (strpos($_SERVER['REQUEST_URI'], 'debug')) {
    $debug = '1';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', E_ALL);
} else {
    $debug = false;
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    ini_set('error_reporting', 0);
}

require '../includes/defaults.inc.php';
require '../config.php';
require '../includes/definitions.inc.php';
require '../includes/functions.php';
require 'includes/functions.inc.php';
require 'includes/authenticate.inc.php';

if (get_client_ip() != $_SERVER['SERVER_ADDR']) {
    if (!$_SESSION['authenticated']) {
        echo 'unauthenticated';
        exit;
    }
}

require 'lib/jpgraph/jpgraph.php';
require 'lib/jpgraph/jpgraph_line.php';
require 'lib/jpgraph/jpgraph_utils.inc.php';
require 'lib/jpgraph/jpgraph_date.php';

if (is_numeric($_GET['bill_id'])) {
    if (get_client_ip() != $_SERVER['SERVER_ADDR']) {
        if (bill_permitted($_GET['bill_id'])) {
            $bill_id = $_GET['bill_id'];
        } else {
            echo 'Unauthorised Access Prohibited.';
            exit;
        }
    } else {
        $bill_id = $_GET['bill_id'];
    }
} else {
    echo 'Unauthorised Access Prohibited.';
    exit;
}

$rate_data    = dbFetchRow('SELECT * from `bills` WHERE `bill_id`= ? LIMIT 1', array($bill_id));
$bill_name = $rate_data['bill_name'];

if (is_numeric($_GET['bill_id']) && is_numeric($_GET[bill_hist_id])) {
    $histrow = dbFetchRow('SELECT UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) AS `to`, rate_95th, rate_average FROM bill_history WHERE bill_id = ? AND bill_hist_id = ?', array($_GET['bill_id'], $_GET['bill_hist_id']));
    if (is_null($histrow)) {
        header("HTTP/1.0 404 Not Found");
        exit();
    }
    $start        = $histrow['from'];
    $end          = $histrow['to'];
    $rate_95th    = $histrow['rate_95th'];
    $rate_average = $histrow['rate_average'];
} else {
    $start        = $_GET[from];
    $end          = $_GET[to];
    $rate_95th    = $rate_data['rate_95th'];
    $rate_average = $rate_data['rate_average'];
}

$xsize = $_GET[x];
$ysize = $_GET[y];
$count = $_GET[count];
$count = ($count + 0);
$iter  = 1;

if ($_GET[type]) {
    $type = $_GET[type];
} else {
    $type = 'date';
}

$dur = ($end - $start);

$datefrom = date('Ymthis', $start);
$dateto   = date('Ymthis', $end);

$dur = ($end - $start);

$counttot = dbFetchCell('SELECT count(`delta`) FROM `bill_data` WHERE `bill_id` = ? AND `timestamp` >= FROM_UNIXTIME( ? ) AND `timestamp` <= FROM_UNIXTIME( ? )', array($bill_id, $start, $end));

$count = round(($dur / 300 / (($ysize - 100) * 3)), 0);
if ($count <= 1) {
    $count = 2;
}

// $count = round($counttot / 260, 0);
// if ($count <= 1) { $count = 2; }
// $max = dbFetchCell("SELECT delta FROM bill_data WHERE bill_id = ? AND `timestamp` >= FROM_UNIXTIME( ? ) AND `timestamp` <= FROM_UNIXTIME( ? ) ORDER BY delta DESC LIMIT 0,1", array($bill_id, $start, $end));
// if ($max > 1000000) { $div = "1000000"; $yaxis = "Mbit/sec";  } else { $div = "1000"; $yaxis = "Kbit/sec"; }
$i = '0';

foreach (dbFetch('SELECT *, UNIX_TIMESTAMP(timestamp) AS formatted_date FROM bill_data WHERE bill_id = ? AND `timestamp` >= FROM_UNIXTIME( ? ) AND `timestamp` <= FROM_UNIXTIME( ? ) ORDER BY timestamp ASC', array($bill_id, $start, $end)) as $row) {
    $timestamp = $row['formatted_date'];
    if (!$first) {
        $first = $timestamp;
    }

    $delta     = $row['delta'];
    $period    = $row['period'];
    $in_delta  = $row['in_delta'];
    $out_delta = $row['out_delta'];
    $in_value  = delta_to_bits($in_delta, $period);
    $out_value = delta_to_bits($out_delta, $period);

    $last = $timestamp;

    $iter_in     += $in_delta;
    $iter_out    += $out_delta;
    $iter_period += $period;

    if ($iter == $count) {
        $out_data[$i]     = round(($iter_out * 8 / $iter_period), 2);
        $out_data_inv[$i] = ($out_data[$i] * -1);
        $in_data[$i]      = round(($iter_in * 8 / $iter_period), 2);
        $tot_data[$i]     = ($out_data[$i] + $in_data[$i]);
        $tot_data_inv[$i] = ($tot_data[$i] * -1);

        if ($tot_data[$i] > $max_value) {
            $max_value = $tot_data[$i];
        }

        $ticks[$i]    = $timestamp;
        $per_data[$i] = $rate_95th;
        $ave_data[$i] = $rate_average;
        $iter         = '1';
        $i++;
        unset($iter_out, $iter_in, $iter_period);
    }

    $iter++;
}//end foreach

$graph_name = date('M j g:ia', $start).' - '.date('M j g:ia', $last);

$n    = count($ticks);
$xmin = $ticks[0];
$xmax = $ticks[($n - 1)];

$graph_name = date('M j g:ia', $xmin).' - '.date('M j g:ia', $xmax);

$graph = new Graph($xsize, $ysize, $graph_name);
$graph->img->SetImgFormat('png');

$graph->SetScale('datlin', 0, 0, $start, $end);

// $graph->title->Set("$graph_name");
$graph->title->SetFont(FF_FONT2, FS_BOLD, 10);
$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);

$graph->xaxis->SetTextLabelInterval(2);

$graph->xaxis->SetPos('min');
// $graph->xaxis->SetLabelAngle(15);
$graph->yaxis->HideZeroLabel(1);
$graph->yaxis->SetFont(FF_FONT1);
$graph->yaxis->SetLabelAngle(0);
$graph->xaxis->title->SetFont(FF_FONT1, FS_NORMAL, 10);
$graph->yaxis->title->SetFont(FF_FONT1, FS_NORMAL, 10);
$graph->yaxis->SetTitleMargin(50);
$graph->xaxis->SetTitleMargin(30);
// $graph->xaxis->HideLastTickLabel();
// $graph->xaxis->HideFirstTickLabel();
// $graph->yaxis->scale->SetAutoMin(1);
$graph->xaxis->title->Set($type);
$graph->yaxis->title->Set('Bits per second');
$graph->yaxis->SetLabelFormatCallback('format_si');


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


$graph->xaxis->SetLabelFormatCallback('TimeCallBack');

$graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#FFFFFF@0.5');
$graph->xgrid->Show(true, true);
$graph->xgrid->SetColor('#e0e0e0', '#efefef');
$graph->SetMarginColor('white');
$graph->SetFrame(false);
$graph->SetMargin(75, 30, 30, 45);
$graph->legend->SetFont(FF_FONT1, FS_NORMAL);

$lineplot = new LinePlot($tot_data, $ticks);
$lineplot->SetLegend('Traffic total');
$lineplot->SetColor('#d5d5d5');
$lineplot->SetFillColor('#d5d5d5@0.5');

// $lineplot2 = new LinePlot($tot_data_inv, $ticks);
// $lineplot2->SetColor("#d5d5d5");
// $lineplot2->SetFillColor("#d5d5d5@0.5");
$lineplot_in = new LinePlot($in_data, $ticks);

$lineplot_in->SetLegend('Traffic In');
$lineplot_in->SetColor('darkgreen');
$lineplot_in->SetFillColor('lightgreen@0.4');
$lineplot_in->SetWeight(1);

$lineplot_out = new LinePlot($out_data_inv, $ticks);
$lineplot_out->SetLegend('Traffic Out');
$lineplot_out->SetColor('darkblue');
$lineplot_out->SetFillColor('lightblue@0.4');
$lineplot_out->SetWeight(1);

if ($_GET['95th']) {
    $lineplot_95th = new LinePlot($per_data, $ticks);
    $lineplot_95th->SetColor('red');
}

if ($_GET['ave']) {
    $lineplot_ave = new LinePlot($ave_data, $ticks);
    $lineplot_ave->SetColor('red');
}

$graph->legend->SetLayout(LEGEND_HOR);
$graph->legend->Pos(0.52, 0.90, 'center');

$graph->Add($lineplot);
// $graph->Add($lineplot2);
$graph->Add($lineplot_in);
$graph->Add($lineplot_out);

if ($_GET['95th']) {
    $graph->Add($lineplot_95th);
}

if ($_GET['ave']) {
    $graph->Add($lineplot_ave);
}

$graph->stroke();
