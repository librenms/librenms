<?php
// A simple Polar graph,

include "../jpgraph.php";
include "../jpgraph_polar.php";


$data = array(0,1,10,2,30,25,40,60,
	      50,110,60,160,70,210,75,230,80,260,85,370,
	      90,480,
	      95,370,100,260,105,230,
	      110,210,120,160,130,110,140,60,
	      150,25,170,2,180,1);

$graph = new PolarGraph(350,400);
$graph->SetScale('log',100);
$graph->SetType(POLAR_180);
//$graph->SetPlotSize(250,250);

// Hide frame around graph (by setting width=0)
$graph->SetFrame(true,'white',1);

// Set plotarea color
$graph->SetColor('lightblue');

// Show both major and minor grid lines
$graph->axis->ShowGrid(true,true);

// Set color for gradient lines
$graph->axis->SetGridColor('lightblue:0.8','lightblue:0.8','lightblue:0.8');

// Setup axis title
$graph->axis->SetTitle('Coverage (in meter)','middle');
$graph->axis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->title->Set('Polar plot #8');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,16);
$graph->title->SetColor('navy');

// Adjust legen box position and color
$graph->legend->SetColor('navy','darkgray');
$graph->legend->SetFillColor('white');
$graph->legend->SetShadow('darkgray@0.5',5);

$p = new PolarPlot($data);
$p->SetFillColor('white@0.5');
$p->mark->SetType(MARK_SQUARE);
$p->SetLegend("Mirophone #1\n(No amps)");

$graph->Add($p);

$graph->Stroke();

?>
