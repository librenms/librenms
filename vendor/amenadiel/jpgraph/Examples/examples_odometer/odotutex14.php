<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(250,160);
$graph->title->Set('Custom scale');
$graph->title->SetColor('white');
$graph->title->SetFont(FF_ARIAL,FS_BOLD);

// Add drop shadow for graph
$graph->SetShadow();

// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
$odo = new Odometer();
$odo->SetColor('lightyellow');

// Setup the scale
$odo->scale->Set(100,600);
$odo->scale->SetTicks(50,2);

// Set display value for the odometer
$odo->needle->Set(280);

// Add drop shadow for needle
$odo->needle->SetShadow();

// Add the odometer to the graph
$graph->Add($odo);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();

?>