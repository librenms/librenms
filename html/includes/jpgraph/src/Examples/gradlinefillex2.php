<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$datay = array(20,10,35,5,17,35,22);

// Setup the graph
$graph = new Graph(400,200);
$graph->SetMargin(40,40,20,30);	
$graph->SetScale("intlin");
$graph->SetBox();
$graph->SetMarginColor('darkgreen@0.8');

// Setup a background gradient image
$graph->SetBackgroundGradient('darkred','yellow',GRAD_HOR,BGRAD_PLOT);

$graph->title->Set('Gradient filled line plot ex2');
$graph->yscale->SetAutoMin(0);

// Create the line
$p1 = new LinePlot($datay);
$p1->SetFillGradient('white','darkgreen');
$graph->Add($p1);

// Output line
$graph->Stroke();

?>


