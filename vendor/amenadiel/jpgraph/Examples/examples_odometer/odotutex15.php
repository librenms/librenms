<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(250,170);

// Setup graph titles
$graph->title->Set('Custom formatting');
$graph->title->SetColor('white');
$graph->title->SetFont(FF_ARIAL,FS_BOLD);

// Add drop shadow for graph
$graph->SetShadow();

// Now we need to create an odometer to add to the graph.
$odo = new Odometer();
$odo->SetColor("lightgray:1.9");

// Setup the scale
$odo->scale->Set(100,600);
$odo->scale->SetTicks(50,2);
$odo->scale->SetTickColor('brown');
$odo->scale->SetTickLength(0.05);
$odo->scale->SetTickWeight(2);

$odo->scale->SetLabelPos(0.75);
$odo->scale->label->SetFont(FF_FONT1, FS_BOLD);
$odo->scale->label->SetColor('brown');
$odo->scale->label->SetFont(FF_ARIAL,FS_NORMAL,10);

// Setup a label with a degree mark
$odo->scale->SetLabelFormat('%dC'.SymChar::Get('degree'));

// Set display value for the odometer
$odo->needle->Set(280);

// Add drop shadow for needle
$odo->needle->SetShadow();

// Add the odometer to the graph
$graph->Add($odo);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();
?>