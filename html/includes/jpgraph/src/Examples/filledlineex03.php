<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$datay = array(11,30,20,13,10,'x',16,12,'x',15,4,9);

// Setup the graph
$graph = new Graph(400,250);
$graph->SetScale('intlin');
$graph->title->Set('Filled line with NULL values');
//Make sure data starts from Zero whatever data we have
$graph->yscale->SetAutoMin(0);

$p1 = new LinePlot($datay);
$p1->SetFillColor('lightblue');
$graph->Add($p1);

// Output line
$graph->Stroke();

?>


