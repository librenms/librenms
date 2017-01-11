<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph
$graph = new OdoGraph(300,320);

// Setup graph titles
$graph->title->Set('Manual positioning');
$graph->title->SetColor('white');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);

// Add drop shadow for graph
$graph->SetShadow();

// Now we need to create an odometer to add to the graph.
$odo1 = new Odometer();
$odo2 = new Odometer();
$odo1->SetColor('lightgray:1.9');
$odo2->SetColor('lightgray:1.9');

// Set display value for the odometer
$odo1->needle->Set(37);
$odo2->needle->Set(73);

// Add drop shadow for needle
$odo1->needle->SetShadow();
$odo2->needle->SetShadow();

// Specify the position for the two odometers
$odo1->SetPos(180,110);
$odo1->SetSize(100);
$odo2->SetPos(110,250);
$odo2->SetSize(100);

// Set captions for the odometers
$odo1->caption->Set("(x,y) = (180,120)\nradius=100");
$odo2->caption->Set("(x,y) = (110,270)\nradius=100");

// Add the odometer to the graph
$graph->Add($odo1);
$graph->Add($odo2);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();
?>