<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$datay = array(20,15,33,5,17,35,22);

// Setup the graph
$graph = new Graph(400,200);
$graph->SetMargin(40,40,20,30);	
$graph->SetScale("intlin");
$graph->SetMarginColor('darkgreen@0.8');

$graph->title->Set('Gradient filled line plot');
$graph->yscale->SetAutoMin(0);

// Create the line
$p1 = new LinePlot($datay);
$p1->SetColor("blue");
$p1->SetWeight(0);
$p1->SetFillGradient('red','yellow');

$graph->Add($p1);

// Output line
$graph->Stroke();

?>


