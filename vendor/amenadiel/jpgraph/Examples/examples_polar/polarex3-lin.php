<?php // content="text/plain; charset=utf-8"
// A simple Polar graph, example 2

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_polar.php');


$data = array(0,1,10,2,30,25,40,60,
	      50,110,60,160,70,210,75,230,80,260,
	      85,270,90,280,
	      95,270,100,260,105,230,
	      110,210,120,160,130,110,140,60,
	      150,25,170,2,180,1);


$graph = new PolarGraph(300,300);
$graph->SetScale('lin',200);
$graph->SetType(POLAR_180);

$graph->title->Set('Polar plot #3');
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->title->SetColor('navy');

$graph->axis->ShowGrid(true,false);

$p = new PolarPlot($data);
$p->SetFillColor('lightred@0.5');

$graph->Add($p);

$graph->Stroke();

?>
