<?php
/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage webinterface
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\BarPlot;
use Amenadiel\JpGraph\Plot\GroupBarPlot;
use Amenadiel\JpGraph\Plot\LinePlot;

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

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (get_client_ip() != $_SERVER['SERVER_ADDR']) {
    if (!$_SESSION['authenticated']) {
        echo 'unauthenticated';
        exit;
    }
}

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

if (is_numeric($_GET['bill_id']) && is_numeric($_GET['bill_hist_id'])) {
    $histrow = dbFetchRow('SELECT UNIX_TIMESTAMP(bill_datefrom) as `from`, UNIX_TIMESTAMP(bill_dateto) AS `to` FROM bill_history WHERE bill_id = ? AND bill_hist_id = ?', array($_GET['bill_id'], $_GET['bill_hist_id']));
    if (is_null($histrow)) {
        header("HTTP/1.0 404 Not Found");
        exit();
    }
    $start        = $histrow['from'];
    $end          = $histrow['to'];
} else {
    $start        = $_GET['from'];
    $end          = $_GET['to'];
}

$xsize = (is_numeric($_GET['x']) ? $_GET['x'] : '800' );
$ysize = (is_numeric($_GET['y']) ? $_GET['y'] : '250' );
// $count        = (is_numeric($_GET['count']) ? $_GET['count'] : "0" );
// $type         = (isset($_GET['type']) ? $_GET['type'] : "date" );
// $dur          = $end - $start;
// $datefrom     = date('Ymthis', $start);
// $dateto       = date('Ymthis', $end);
$imgtype    = (isset($_GET['type']) ? $_GET['type'] : 'historical' );
$imgbill    = (isset($_GET['imgbill']) ? $_GET['imgbill'] : false);
$yaxistitle = 'Bytes';

$in_data      = array();
$out_data     = array();
$tot_data     = array();
$allow_data   = array();
$ave_data     = array();
$overuse_data = array();
$ticklabels   = array();

if ($imgtype == 'historical') {
    $i = '0';

    foreach (dbFetchRows('SELECT * FROM `bill_history` WHERE `bill_id` = ? ORDER BY `bill_datefrom` DESC LIMIT 12', array($bill_id)) as $data) {
        $datefrom          = strftime('%e %b %Y', strtotime($data['bill_datefrom']));
        $dateto        = strftime('%e %b %Y', strtotime($data['bill_dateto']));
        $datelabel     = $datefrom."\n".$dateto;
        $traf['in']    = $data['traf_in'];
        $traf['out']   = $data['traf_out'];
        $traf['total'] = $data['traf_total'];

        if ($data['bill_type'] == 'Quota') {
            $traf['allowed'] = $data['bill_allowed'];
            $traf['overuse'] = $data['bill_overuse'];
        } else {
            $traf['allowed'] = '0';
            $traf['overuse'] = '0';
        }

        array_push($ticklabels, $datelabel);
        array_push($in_data, $traf['in']);
        array_push($out_data, $traf['out']);
        array_push($tot_data, $traf['total']);
        array_push($allow_data, $traf['allowed']);
        array_push($overuse_data, $traf['overuse']);
        $i++;
        // print_r($data);
    }//end foreach

    if ($i < 12) {
        $y = (12 - $i);
        for ($x = 0; $x < $y; $x++) {
            $allowed = (($x == '0') ? $traf['allowed'] : '0' );
            array_push($in_data, '0');
            array_push($out_data, '0');
            array_push($tot_data, '0');
            array_push($allow_data, $allowed);
            array_push($overuse_data, '0');
            array_push($ticklabels, '');
        }
    }

    $yaxistitle = 'Gigabytes';
    $graph_name = 'Historical bandwidth over the last 12 billing periods';
} else {
    $data    = array();
    $average = 0;
    if ($imgtype == 'day') {
        foreach (dbFetch('SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM bill_data WHERE `bill_id` = ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY DATE(timestamp) ORDER BY timestamp ASC', array($bill_id, $start, $end)) as $data) {
            $traf['in']        = (isset($data['traf_in']) ? $data['traf_in'] : 0);
            $traf['out']   = (isset($data['traf_out']) ? $data['traf_out'] : 0);
            $traf['total'] = (isset($data['traf_total']) ? $data['traf_total'] : 0);
            $datelabel     = strftime("%e\n%b", $data['timestamp']);
            array_push($ticklabels, $datelabel);
            array_push($in_data, $traf['in']);
            array_push($out_data, $traf['out']);
            array_push($tot_data, $traf['total']);
            $average += $data['traf_total'];
        }

        $ave_count = count($tot_data);
        if ($imgbill != false) {
            $days = (strftime('%e', date($end - $start)) - $ave_count - 1);
            for ($x = 0; $x < $days; $x++) {
                array_push($ticklabels, '');
                array_push($in_data, 0);
                array_push($out_data, 0);
                array_push($tot_data, 0);
            }
        }
    } elseif ($imgtype == 'hour') {
        foreach (dbFetch('SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM bill_data WHERE `bill_id` = ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC', array($bill_id, $start, $end)) as $data) {
            $traf['in']    = (isset($data['traf_in']) ? $data['traf_in'] : 0);
            $traf['out']   = (isset($data['traf_out']) ? $data['traf_out'] : 0);
            $traf['total'] = (isset($data['traf_total']) ? $data['traf_total'] : 0);
            $datelabel     = strftime('%H:%M', $data['timestamp']);
            array_push($ticklabels, $datelabel);
            array_push($in_data, $traf['in']);
            array_push($out_data, $traf['out']);
            array_push($tot_data, $traf['total']);
            $average += $data['traf_total'];
        }

        $ave_count = count($tot_data);
    }//end if

    $decimal = 0;
    $average = ($average / $ave_count);
    for ($x = 0; $x <= count($tot_data); $x++) {
        array_push($ave_data, $average);
    }

    $graph_name = date('M j g:ia', $start).' - '.date('M j g:ia', $end);
}//end if

// Create the graph. These two calls are always required
$graph = new Graph($xsize, $ysize, $graph_name);
$graph->img->SetImgFormat('png');

// $graph->SetScale("textlin",0,0,$start,$end);
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
$graph->xaxis->SetTickLabels($ticklabels);
$graph->xgrid->Show(true, true);
$graph->xgrid->SetColor('#e0e0e0', '#efefef');

$graph->yaxis->SetFont(FF_FONT1);
$graph->yaxis->SetTitleMargin(50);
$graph->yaxis->title->SetFont(FF_FONT1, FS_NORMAL, 10);
$graph->yaxis->title->Set('Bytes Transferred');
$graph->yaxis->SetLabelFormatCallback('format_bytes_billing');
$graph->ygrid->SetFill(true, '#EFEFEF@0.5', '#FFFFFF@0.5');

// Create the bar plots
$barplot_tot = new BarPlot($tot_data);
$barplot_tot->SetLegend('Traffic total');
$barplot_tot->SetColor('darkgray');
$barplot_tot->SetFillColor('lightgray@0.4');
$barplot_tot->value->Show();
$barplot_tot->value->SetFormatCallback('format_bytes_billing_short');

$barplot_in = new BarPlot($in_data);
$barplot_in->SetLegend('Traffic In');
$barplot_in->SetColor('darkgreen');
$barplot_in->SetFillColor('lightgreen@0.4');
$barplot_in->SetWeight(1);

$barplot_out = new BarPlot($out_data);
$barplot_out->SetLegend('Traffic Out');
$barplot_out->SetColor('darkblue');
$barplot_out->SetFillColor('lightblue@0.4');
$barplot_out->SetWeight(1);

if ($imgtype == 'historical') {
    $barplot_over = new BarPlot($overuse_data);
    $barplot_over->SetLegend('Traffic Overusage');
    $barplot_over->SetColor('darkred');
    $barplot_over->SetFillColor('lightred@0.4');
    $barplot_over->SetWeight(1);

    $lineplot_allow = new LinePlot($allow_data);
    $lineplot_allow->SetLegend('Traffic Allowed');
    $lineplot_allow->SetColor('black');
    $lineplot_allow->SetWeight(1);

    $gbplot = new GroupBarPlot(array($barplot_in, $barplot_tot, $barplot_out, $barplot_over));
} else {
    $lineplot_allow = new LinePlot($ave_data);
    // $lineplot_allow->SetLegend("Average per ".$imgtype);
    $lineplot_allow->SetLegend('Average');
    $lineplot_allow->SetColor('black');
    $lineplot_allow->SetWeight(1);

    $gbplot = new GroupBarPlot(array($barplot_in, $barplot_tot, $barplot_out));
}//end if

$graph->Add($gbplot);
$graph->Add($lineplot_allow);

// Display the graph
$graph->Stroke();
