<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_utils.inc.php");

$f = new FuncGenerator('cos($x)*$x');
list($xdata,$ydata) = $f->E(-1.2*M_PI,1.2*M_PI);

$f = new FuncGenerator('$x*$x');
list($x2data,$y2data) = $f->E(-2,2);

// Setup the basic graph
$graph = new Graph(450,350,"auto");
$graph->SetScale("linlin");
$graph->SetShadow();
$graph->img->SetMargin(50,50,60,40);	
$graph->SetBox(true,'black',2);	
$graph->SetMarginColor('white');
$graph->SetColor('lightyellow');

// ... and titles
$graph->title->Set('Example of Function plot');
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->subtitle->Set("(With some more advanced axis formatting\nHiding first and last label)");
$graph->subtitle->SetFont(FF_FONT1,FS_NORMAL);
$graph->xgrid->Show();

$graph->yaxis->SetPos(0);
$graph->yaxis->SetWeight(2);
$graph->yaxis->HideZeroLabel();
$graph->yaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->SetColor('black','darkblue');
$graph->yaxis->HideTicks(true,false);
$graph->yaxis->HideFirstLastLabel();

$graph->xaxis->SetWeight(2);
$graph->xaxis->HideZeroLabel();
$graph->xaxis->HideFirstLastLabel();
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->SetColor('black','darkblue');

$lp1 = new LinePlot($ydata,$xdata);
$lp1->SetColor('blue');
$lp1->SetWeight(2);

$lp2 = new LinePlot($y2data,$x2data);
list($xm,$ym)=$lp2->Max();
$lp2->SetColor('red');
$lp2->SetWeight(2);


$graph->Add($lp1);
$graph->Add($lp2);
$graph->Stroke();

?>


