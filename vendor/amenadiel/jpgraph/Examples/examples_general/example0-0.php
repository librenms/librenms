<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Some (random) data
$ydata = array(11, 3, 8, 12, 5, 1, 9, 13, 5, 7);

// Size of the overall graph
$width  = 350;
$height = 250;

// Create the graph and set a scale.
// These two calls are always required
$graph = new Graph\Graph($width, $height);
$graph->SetScale('intlin');

// Create the linear plot
$lineplot = new Plot\LinePlot($ydata);

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
