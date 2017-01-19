<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(250,200);

// Setup titles
$graph->title->Set("Result for 2002");
$graph->title->SetColor("white");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);
$graph->subtitle->Set("New York Office");
$graph->subtitle->SetColor("white");
$graph->caption->Set("Figure 1.This is a very, very\nlong text with multiples lines\nthat are added as a caption.");
$graph->caption->SetColor("white");

// Setup colors
// Make the border 40% darker than normal "khaki"
$graph->SetMarginColor("khaki:0.6");
$graph->SetColor("khaki");

// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
$odo = new Odometer();

// Setup colors for odometyer plot
$odo->SetColor('white');
$odo->scale->label->SetColor("darkred");
$odo->needle->SetFillColor("yellow");

// Set display value for the odometer
$odo->needle->Set(30);

// Add the odometer to the graph
$graph->Add($odo);

// ... and finally stroke and stream the image back to the client
$graph->Stroke();

?>
