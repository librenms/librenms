<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_utils.inc.php");

$f = new FuncGenerator('cos($i)','$i*$i*$i');
list($xdata,$ydata) = $f->E(-M_PI,M_PI,25);

$graph = new Graph(350,430,"auto");
$graph->SetScale("linlin");
$graph->SetShadow();
$graph->img->SetMargin(50,50,60,40);	
$graph->SetBox(true,'black',2);	
$graph->SetMarginColor('white');
$graph->SetColor('lightyellow');
$graph->SetAxisStyle(AXSTYLE_BOXIN);
$graph->xgrid->Show();


//$graph->xaxis->SetLabelFormat('%.0f');

$graph->img->SetMargin(50,50,60,40);		

$graph->title->Set("Function plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->subtitle->Set("(BOXIN Axis style)");
$graph->subtitle->SetFont(FF_FONT1,FS_NORMAL);

$lp1 = new LinePlot($ydata,$xdata);
$lp1->SetColor("blue");
$lp1->SetWeight(2);

$graph->Add($lp1);
$graph->Stroke();
?>


