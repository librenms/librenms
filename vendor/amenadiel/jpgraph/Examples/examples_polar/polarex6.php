<?php // content="text/plain; charset=utf-8"
// A simple Polar graph,

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_polar.php');


$data = array(0,1,10,2,30,25,40,60,
	      50,110,60,160,70,210,75,230,80,260,85,270,
	      90,480,
	      95,270,100,260,105,230,
	      110,210,120,160,130,110,140,60,
	      150,25,170,2,180,1);


$graph = new PolarGraph(300,350);
$graph->SetScale('log');
$graph->SetType(POLAR_180);

// Show both major and minor grid lines
$graph->axis->ShowGrid(true,true);

$graph->title->Set('Polar plot #6');
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->title->SetColor('navy');


$p = new PolarPlot($data);
$p->SetFillColor('lightred@0.5');

$graph->Add($p);

$graph->Stroke();

?>
