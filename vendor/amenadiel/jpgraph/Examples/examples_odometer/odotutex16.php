<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(350,100);

// Add drop shadow for graph
$graph->SetShadow();

// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
$odo1 = new Odometer();
$odo2 = new Odometer();
$odo1->SetColor("lightgray:1.9");
$odo2->SetColor("lightgray:1.9");

// Adjust start and end angle for the scale
$odo2->scale->SetAngle(110,250);

$odo1->scale->label->SetFont(FF_ARIAL,FS_BOLD,10);
$odo2->scale->label->SetFont(FF_ARIAL,FS_BOLD,10);

// Set display value for the odometer
$odo1->needle->Set(70);
$odo2->needle->Set(70);

// Add drop shadow for needle
$odo1->needle->SetShadow();
$odo2->needle->SetShadow();

// Specify the layout for the two odometers
$row = new LayoutHor( array($odo1,$odo2) );

// Add the odometer to the graph
$graph->Add($row);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();
?>