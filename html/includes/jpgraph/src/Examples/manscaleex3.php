<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$ydata = array(12,17,22,19,5,15);

$graph = new Graph(250,200);
$graph->SetScale("textlin",3,35);
$graph->SetTickDensity(TICKD_DENSE);
$graph->yscale->SetAutoTicks();

$graph->title->Set('Manual scale, auto ticks');
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$line = new LinePlot($ydata);
$graph->Add($line);

// Output graph
$graph->Stroke();

?>


