<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_utils.inc.php';

$f = new FuncGenerator('cos($i)', '$i*$i*$i');
list($xdata, $ydata) = $f->E(-M_PI, M_PI, 25);

$graph = new Graph\Graph(380, 450);
$graph->SetScale("linlin");
$graph->SetShadow();
$graph->img->SetMargin(50, 50, 60, 40);
$graph->SetBox(true, 'black', 2);
$graph->SetMarginColor('white');
$graph->SetColor('lightyellow');
$graph->SetAxisStyle(AXSTYLE_SIMPLE);

//$graph->xaxis->SetLabelFormat('%.1f');

$graph->title->Set("Function plot with marker");
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->subtitle->Set("(BOXOUT Axis style)");
$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);

$lp1 = new Plot\LinePlot($ydata, $xdata);
$lp1->mark->SetType(MARK_FILLEDCIRCLE);
$lp1->mark->SetFillColor("red");
$lp1->SetColor("blue");

$graph->Add($lp1);
$graph->Stroke();
