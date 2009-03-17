<?php
// A simple Polar graph, example 2

include "../jpgraph.php";
include "../jpgraph_polar.php";


$data = array(0,1,10,2,30,25,40,60,
	      50,110,60,160,70,210,75,230,80,260,
	      85,270,90,280,
	      95,270,100,260,105,230,
	      110,210,120,160,130,110,140,60,
	      150,25,170,2,180,1);


$graph = new PolarGraph(300,350);
$graph->SetScale('log');

// Show both major and minor grid lines
$graph->axis->ShowGrid(true,true);

$graph->title->Set('Polar plot #5');
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->title->SetColor('navy');

// Hide last labels on the Radius axis
// They intersect with the box otherwise
$graph->axis->HideLastTickLabel();

$p = new PolarPlot($data);
$p->SetFillColor('lightred@0.5');

$graph->Add($p);

$graph->Stroke();

?>
