<?php
// A simple Polar graph, example 0

include "../jpgraph.php";
include "../jpgraph_polar.php";


$data = array(0,1,10,2,30,25,40,60,
	      50,110,60,160,70,210,75,230,80,260,
	      85,270,90,280,
	      95,270,100,260,105,230,
	      110,210,120,160,130,110,140,60,
	      150,25,170,2,180,1);


$graph = new PolarGraph(250,250);
$graph->SetScale('lin');
$graph->SetMargin(35,35,25,25);

$p = new PolarPlot($data);
$p->SetFillColor('lightblue@0.5');
$graph->Add($p);

$graph->Stroke();

?>
