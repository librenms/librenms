<?php
include ("../jpgraph.php");
include ("../jpgraph_scatter.php");

$datay = array(20,22,12,13,17,20,16,19,30,31,40,43);

$graph = new Graph(300,200,"auto");
$graph->SetScale("textlin");

$graph->SetShadow();
$graph->img->SetMargin(40,40,40,40);		

$graph->title->Set("Simple mpuls plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$sp1 = new ScatterPlot($datay);
$sp1->mark->SetType(MARK_SQUARE);
$sp1->SetImpuls();

$graph->Add($sp1);
$graph->Stroke();

?>