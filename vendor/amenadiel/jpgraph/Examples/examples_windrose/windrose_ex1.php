<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
$data = array(
    0 => array(1, 1, 2.5, 4),
    1 => array(3, 4, 1, 4),
    'wsw' => array(1, 5, 5, 3),
    'N' => array(2, 7, 5, 4, 2),
    15 => array(2, 7, 12));

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 400);

// Setup title
$graph->title->Set('Windrose basic example');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);
$wp->SetRadialGridStyle('solid');
$graph->Add($wp);

// Send the graph to the browser
$graph->Stroke();
