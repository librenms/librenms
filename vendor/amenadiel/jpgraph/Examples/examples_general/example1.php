<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$ydata = array(11, 3, 8, 12, 5, 1, 9, 13, 5, 7);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(300, 200);
$graph->SetScale("textlin");
$graph->img->SetMargin(50, 90, 40, 50);
$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);
$graph->title->Set("Examples for graph");

// Create the linear plot
$lineplot = new Plot\LinePlot($ydata);
$lineplot->SetLegend("Test 1");
$lineplot->SetColor("blue");

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
