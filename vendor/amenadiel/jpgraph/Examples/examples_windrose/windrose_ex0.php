<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data = array(
    0 => array(5, 5, 5, 8),
    1 => array(3, 4, 1, 4),
    'WSW' => array(1, 5, 5, 3),
    'N' => array(2, 3, 8, 1, 1),
    15 => array(2, 3, 5));

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 400);
$graph->title->Set('A basic Windrose graph');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Add and send back to browser
$graph->Add($wp);
$graph->Stroke();
