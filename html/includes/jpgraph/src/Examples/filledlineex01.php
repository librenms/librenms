<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$datay = array(1.23,1.9,1.6,3.1,3.4,2.8,2.1,1.9);
$graph = new Graph(300,200,"auto");
$graph->SetScale("textlin");

$graph->img->SetMargin(40,40,40,40);	
$graph->SetShadow();

$graph->title->Set("Example of filled line plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new LinePlot($datay);
$p1->SetFillColor("orange");
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);
$graph->Add($p1);

$graph->Stroke();
?>
