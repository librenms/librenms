<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
require_once 'jpgraph/jpgraph_bar.php';

$datay = array(12, 8, 19, 3, 10, 5);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(300, 200);
$graph->SetScale('textlin');

// Add a drop shadow
$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(40, 30, 40, 40);

// Create a bar pot
$bplot = new Plot\BarPlot($datay);
$graph->Add($bplot);

// Create and add a new text
$txt = new Text('This is a text');
$txt->SetPos(0, 20);
$txt->SetColor('darkred');
$txt->SetFont(FF_FONT2, FS_BOLD);
$graph->AddText($txt);

// Setup the titles
$graph->title->Set("A simple bar graph");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

// Display the graph
$graph->Stroke();
