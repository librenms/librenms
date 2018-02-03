<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$data = array(
    0     => array(1, 1, 2.5, 4),
    1     => array(3, 4, 1, 4),
    'wsw' => array(1, 5, 5, 3),
    'N'   => array(2, 7, 5, 4, 2),
    15    => array(2, 7, 12));

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 400);

// Creta an icon to be added to the graph
$icon = new IconPlot('tornado.jpg', 10, 10, 1.3, 50);
$icon->SetAnchor('left', 'top');
$graph->Add($icon);

// Setup title
$graph->title->Set('Windrose icon example');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Add to graph and send back to client
$graph->Add($wp);
$graph->Stroke();
