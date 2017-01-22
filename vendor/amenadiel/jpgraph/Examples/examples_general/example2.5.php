<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$ydata = array(11, -3, -8, 7, 5, -1, 9, 13, 5, -7);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(300, 200);
$graph->SetScale("textlin");

// Create the linear plot
$lineplot = new Plot\LinePlot($ydata);

// Add the plot to the graph
$graph->Add($lineplot);

$graph->img->SetMargin(40, 20, 20, 40);
$graph->title->Set("Example 2.5");
$graph->xaxis->title->Set("X-title");
$graph->xaxis->SetPos("min");
$graph->yaxis->title->Set("Y-title");

// Display the graph
$graph->Stroke();
