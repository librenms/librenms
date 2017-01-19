<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
require_once 'jpgraph/jpgraph_bar.php';

$datay = array(12, 8, 19, 3, 10, 5);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(300, 200);
$graph->SetScale("textlin");
$graph->yaxis->scale->SetGrace(20);

// Add a drop shadow
$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(40, 30, 20, 40);

// Create a bar pot
$bplot = new Plot\BarPlot($datay);

// Adjust fill color
$bplot->SetFillColor('orange');
$bplot->value->Show();
$graph->Add($bplot);

// Setup the titles
$graph->title->Set("Bar graph with Y-scale grace");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

// Display the graph
$graph->Stroke();
