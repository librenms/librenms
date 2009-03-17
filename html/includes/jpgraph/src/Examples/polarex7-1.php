<?php
// A simple Polar graph,

include "../jpgraph.php";
include "../jpgraph_polar.php";


$data = array(0,1,30,2,30,25,40,60,
	      50,110,60,160,70,210,75,230,80,260,85,370,
	      90,480,
	      95,370,100,260,105,230,
	      110,210,120,160,130,110,140,60,
	      150,25,150,2,180,1);

$graph = new PolarGraph(300,350);
$graph->SetScale('log',100);
$graph->SetType(POLAR_180);
$graph->SetPlotSize(220,250);

// Hide frame around graph (by setting width=0)
$graph->SetFrame(true,'white',1);

$graph->SetBackgroundGradient('blue:1.3','brown:1.4',GRAD_HOR,BGRAD_PLOT);

// Set color for gradient lines
$graph->axis->SetGridColor('gray','gray','gray');

// Setup axis title
$graph->axis->SetTitle('Coverage (in meter)','middle');
$graph->axis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->title->Set('Polar plot #7');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,16);
$graph->title->SetColor('navy');

// Adjust legen box position and color
$graph->legend->SetColor('navy','darkgray');
$graph->legend->SetFillColor('white');
$graph->legend->SetShadow('darkgray@0.5',5);

$p = new PolarPlot($data);
$p->SetFillColor('lightblue@0.5');
$p->mark->SetType(MARK_SQUARE);
$p->SetLegend("Mirophone #1\n(No amps)");

$graph->Add($p);

$graph->Stroke();

?>
