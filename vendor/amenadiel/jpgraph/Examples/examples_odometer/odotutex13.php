<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(250,150);

$graph->title->Set('Example with scale indicators');

// Add drop shadow for graph
$graph->SetShadow();

// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
$odo = new Odometer(ODO_HALF);

// Add color indications
$odo->AddIndication(0,20,"green:0.7");
$odo->AddIndication(20,30,"green:0.9");
$odo->AddIndication(30,60,"yellow");
$odo->AddIndication(60,80,"orange");
$odo->AddIndication(80,100,"red");

$odo->SetCenterAreaWidth(0.45);

// Set display value for the odometer
$odo->needle->Set(90);

// Add scale labels
$odo->label->Set("mBar");
$odo->label->SetFont(FF_FONT2,FS_BOLD);

// Add drop shadow for needle
$odo->needle->SetShadow();

// Add the odometer to the graph
$graph->Add($odo);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();
?>