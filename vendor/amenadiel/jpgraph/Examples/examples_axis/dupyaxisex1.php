<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_utils.inc.php';

$f                   = new FuncGenerator('cos($i)', '$i*$i*$i');
list($xdata, $ydata) = $f->E(-M_PI, M_PI, 25);

$graph = new Graph\Graph(300, 200);
$graph->SetScale("linlin");
$graph->SetMargin(50, 50, 20, 30);
$graph->SetFrame(false);
$graph->SetBox(true, 'black', 2);
$graph->SetMarginColor('white');
$graph->SetColor('lightyellow');

$graph->title->Set('Duplicating Y-axis');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$graph->SetAxisStyle(AXSTYLE_YBOXIN);
$graph->xgrid->Show();

$lp1 = new Plot\LinePlot($ydata, $xdata);
$lp1->SetColor("blue");
$lp1->SetWeight(2);
$graph->Add($lp1);

$graph->Stroke();
