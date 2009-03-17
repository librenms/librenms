<?php
include ("../jpgraph.php");
include ("../jpgraph_scatter.php");

$datax = array(3.5,3.7,3,4,6.2,6,3.5,8,14,8,11.1,13.7);
$datay = array(20,22,12,13,17,20,16,19,30,31,40,43);

$graph = new Graph(300,200,"auto");
$graph->SetScale("linlin");

$graph->Set90AndMargin(40,40,40,40);		
$graph->SetShadow();

$graph->title->Set("A 90 degrees rotated scatter plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Adjust the label align for X-axis so they look good rotated
$graph->xaxis->SetLabelAlign('right','center','right');

// Adjust the label align for Y-axis so they look good rotated
$graph->yaxis->SetLabelAlign('center','bottom');

$graph->xaxis->SetTitle('X-Axis title','low');
$graph->xaxis->title->SetAngle(90);
$graph->xaxis->title->SetMargin(15);

$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor("red");
$sp1->mark->SetWidth(5);

$graph->Add($sp1);
$graph->Stroke();

?>
