<?php

if($_GET['debug']) {
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
} else { ini_set('display_errors', 0); }

include("../includes/defaults.inc.php");
include("../config.php");
include("../includes/functions.php");
include("includes/authenticate.inc.php");
if(!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }
require("includes/jpgraph/src/jpgraph.php");
include("includes/jpgraph/src/jpgraph_line.php");
include("includes/jpgraph/src/jpgraph_utils.inc.php");

if($_GET['bill_id']){
        if(testPassword($_GET['bill_id'],$_GET['bill_code']) == "1") {
                $bill_id = $_GET['bill_id'];
        } else {
                echo("Unauthorised Access Prohibited.");
		exit;
        }
} else {
	echo("Unauthorised Access Prohibited.");
	exit;
}

$start		= $_GET[from];
$end		= $_GET[to];
$xsize		= $_GET[x];
$ysize		= $_GET[y];
$count		= $_GET[count];
$count = $count + 0;
$iter = 1;

if ($_GET[type]) { $type = $_GET[type]; } else { $type = "date"; }

$dur = $end - $start;

if($type == "date") { $date_format = "%d %b %H:%i"; $tickinterval = "2"; } else { $date_format = "%H"; $tickinterval = "1"; }

$datefrom = date('Ymt', $start) . "000000";
$dateto = date('Ymt',   $end) . "235959";

$datefrom = mysql_result(mysql_query("SELECT FROM_UNIXTIME($start, '%Y%m%d')"),0) . "000000";
$dateto = mysql_result(mysql_query("SELECT FROM_UNIXTIME($end, '%Y%m%d')"),0) . "235959";

$rate_data	= getRates($bill_id,$datefrom,$dateto);
$rate_95th	= $rate_data['rate_95th'] * 1000;
$rate_average   = $rate_data['rate_average'] * 1000;

$bi_q 		= mysql_query("SELECT * FROM bills WHERE bill_id = $bill_id");
$bi_a 		= mysql_fetch_array($bi_q);
$bill_name  	= $bi_a['bill_name'];

$countsql = mysql_query("SELECT count(`delta`) FROM `bill_data` WHERE `bill_id` = '$bill_id' AND `timestamp` >= '$datefrom' AND `timestamp` <= '$dateto'");
$counttot = mysql_result($countsql,0);

$count = round($counttot / (($ysize - 100) * 2), 0);
if ($count <= 1) { $count = 2; }


#$count = 8;

#$count = round($counttot / 260, 0);
#if ($count <= 1) { $count = 2; }

$max = mysql_result(mysql_query("SELECT delta FROM bill_data WHERE bill_id = $bill_id AND timestamp >= $datefrom AND timestamp <= $dateto ORDER BY delta DESC LIMIT 0,1"),0);
if($max > 1000000) { $div = "1000000"; $yaxis = "Mbit/sec";  } else { $div = "1000"; $yaxis = "Kbit/sec"; }

$i = '0';

#$start = mysql_result(mysql_query("SELECT *, UNIX_TIMESTAMP(timestamp) AS formatted_date FROM bill_data WHERE bill_id = $bill_id AND timestamp >=$datefrom AND timestamp <= $dateto ORDER BY timestamp ASC LIMIT 0,1"),0);
#$end   = mysql_result(mysql_query("SELECT *, UNIX_TIMESTAMP(timestamp) AS formatted_date FROM bill_data WHERE bill_id = $bill_id AND timestamp >=$datefrom AND timestamp <= $dateto ORDER BY timestamp DESC LIMIT 0,1"),0);

$dur = $end - $start; 

$sql = "SELECT *, UNIX_TIMESTAMP(timestamp) AS formatted_date FROM bill_data WHERE bill_id = $bill_id AND timestamp >= $datefrom AND timestamp <= $dateto ORDER BY timestamp ASC";
$data = mysql_query($sql);
while($row = mysql_fetch_array($data)) 
{ 

	@$timestamp = $row['formatted_date'];
	if (!$first) { $first = $timestamp; }
	@$delta = $row['delta'];
	@$period = $row['period'];
	@$in_delta = $row['in_delta'];
        @$out_delta = $row['out_delta'];
	@$in_value = round($in_delta * 8 / $period / $div, 2);
        @$out_value = round($out_delta * 8 / $period / $div, 2);

	#@$data[] = $in_value + $out_value;
	#@$in_data[] = $in_value;
        #@$out_data[] = $out_value;
	#@$ticks[] = $timestamp;
	#@$per_data[] = $rate_95th / 1000;
	#@$ave_data[] = $rate_average / 1000;

	@$last = $timestamp;

        $iter_in        = $iter_in + $in_delta;
        $iter_out       = $iter_out + $out_delta;
        $iter_period    = $iter_period + $period;

	if ($iter == $count) {

		$out_data[$i] 	= round($iter_out * 8 / $iter_period / $div, 2);
		$in_data[$i] 	= round($iter_in * 8 / $iter_period / $div, 2);
		$tot_data[$i]   = $out_data[$i] + $in_data[$i];
#		$ticks[$i] 	= date('M j g:ia', $timestamp);
		$ticks[$i]	= $timestamp;
		
          if($dur < 172800) {
              $hour = date('h', $timestamp);
              if($hour != $lasthour) { $tickPositions[] = $i; $tickLabels[] = date('ga', $timestamp); }
              $lasthour = $hour;

          } elseif ($dur < 604800) {
		$day = date('d', $timestamp);
		if($day != $lastday) { $tickPositions[] = $i; $tickLabels[] = date('D', $timestamp); $h = 0; }
		$lastday = $day;

                $hour = trim(date('g', $timestamp));
                if($hour != $lasthour) {  
                  if($hour == '12') {$tickMinPositions[] = $i; $h = 0;  } $h++; 
                }
		$lasthour = $hour;


          } else {
                $day = date('d', $timestamp);
                if($day != $lastday) { $tickPositions[] = $i; $tickLabels[] = date('dS', $timestamp); }
                $lastday = $day;
          }

  		$per_data[$i]   = $rate_95th / $div;
        	$ave_data[$i]   = $rate_average / $div;
		$timestamps[$i]	= $timestamp;
		$iter           = "1"; 
                $i++;
		unset($iter_out, $iter_in, $iter_period);
        }

	$iter++;

}

#print_r($ticks);
#print_r($tot_data);

$graph_name = date('M j g:ia', $start) . " - " . date('M j g:ia', $last);

$n = count($ticks);
$xmin = $ticks[0];
$xmax = $ticks[$n-1];

$graph_name = date('M j g:ia', $xmin) . " - " . date('M j g:ia', $xmax);


$graph = new Graph($xsize, $ysize, $graph_name);
$graph->img->SetImgFormat("png");
#$graph->img->SetAntiAliasing(true);
$graph->SetScale( "intlin");
#$graph->SetScale('intlin',0,0,$xmin,$xmax);
$graph->title->Set("$graph_name"); 
$graph->title->SetFont(FF_FONT2,FS_BOLD,10);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);

$graph->xaxis->SetTickLabels($ticks);

if(count($tickPositions) > 24) {
  $graph->xaxis->SetTextLabelInterval(3);
}elseif(count($tickPositions) > 12) {
  $graph->xaxis->SetTextLabelInterval(2);
}

$graph->xaxis->SetPos('min');
#$graph->xaxis->SetLabelAngle(15);
$graph->yaxis->HideZeroLabel(1);
$graph->yaxis->SetFont(FF_FONT1);
$graph->yaxis->SetLabelAngle(0);
$graph->xaxis->title->SetFont(FF_FONT1,FS_NORMAL,10);
$graph->yaxis->title->SetFont(FF_FONT1,FS_NORMAL,10);
$graph->yaxis->SetTitleMargin(50);
$graph->xaxis->SetTitleMargin(30); 
#$graph->xaxis->HideLastTickLabel();
#$graph->xaxis->HideFirstTickLabel(); 
#$graph->yaxis->scale->SetAutoMin(1);
#$graph->xaxis->title->Set("$type");
$graph->yaxis->title->Set("$yaxis");

$graph->xaxis->SetTickPositions($tickPositions,$tickMinPositions,$tickLabels);
$graph->xaxis->SetMajTickPositions($tickPositions,$tickLabels);

$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#FFFFFF@0.5');
$graph->xgrid->Show(true,true);
$graph->xgrid->SetColor('#e0e0e0','#efefef');
$graph->SetMarginColor('white');
$graph->SetFrame(false);
$graph->SetMargin(75,30,30,45);
$graph->legend->SetFont(FF_FONT1,FS_NORMAL);

$lineplot = new LinePlot($tot_data);
#$lineplot->SetLegend("Traffic total");
$lineplot->SetColor("#d5d5d5");
$lineplot->SetFillColor("#d5d5d5");
$lineplot_in = new LinePlot($in_data);
#$lineplot_in->SetLegend("Traffic In");
$lineplot_in->SetColor("#009900");
$lineplot_out = new LinePlot($out_data);
#$lineplot_out->SetLegend("Traffic Out");
$lineplot_out->SetColor("blue");

if($_GET['95th']) {
 $lineplot_95th = new LinePlot($per_data);
 $lineplot_95th ->SetColor("red");
}

if($_GET['ave']) {
 $lineplot_ave = new LinePlot($ave_data);
 $lineplot_ave ->SetColor("red");
}

#$graph->legend->SetLayout(LEGEND_HOR);
#$graph->legend->Pos(0.52, 0.85, 'center'); 

$graph->Add($lineplot);
$graph->Add($lineplot_in);
$graph->Add($lineplot_out);
if($_GET['95th']) {
 $graph->Add($lineplot_95th);
}
if($_GET['ave']) {
 $graph->Add($lineplot_ave);
}

$graph->stroke();

?>
