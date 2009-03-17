<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_utils.inc.php");

$f = new FuncGenerator('cos($i)','$i*$i*$i');
list($xdata,$ydata) = $f->E(-M_PI,M_PI,25);

$graph = new Graph(300,200,"auto");
$graph->SetScale("linlin");
$graph->SetMargin(50,50,20,30);	
$graph->SetFrame(false);
$graph->SetBox(true,'black',2);	
$graph->SetMarginColor('white');
$graph->SetColor('lightyellow');

$graph->title->Set('Duplicating Y-axis');
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$graph->SetAxisStyle(AXSTYLE_YBOXIN);
$graph->xgrid->Show();

$lp1 = new LinePlot($ydata,$xdata);
$lp1->SetColor("blue");
$lp1->SetWeight(2);
$graph->Add($lp1);

$graph->Stroke();
?>


