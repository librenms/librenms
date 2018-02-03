<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Data can be specified using both ordinal index of the axis
// as well as the direction label.
$data = array(
    'E'  => array(3, 2, 1, 2, 2),
    'N'  => array(1, 1, 1.5, 2),
    'nw' => array(1, 1, 1.5, 2),
    'S'  => array(2, 3, 5, 1),
);

// Define the color,weight and style of some individual radial
// grid lines. Axis can be specified either by their (localized)
// label or by their index.
// Note; Depending on how many axis you have in the plot the
// index will vary between 0..n where n is the number of
// compass directions.
$axiscolors  = array('nw' => 'brown');
$axisweights = array('nw' => 8); // Could also be specified as 6 => 8
$axisstyles  = array('nw' => 'solid');

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 500);
$graph->title->Set('Windrose example 9');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);
$graph->title->SetColor('navy');

// Create the free windrose plot.
$wp = new Plot\WindrosePlot($data);
$wp->SetType(WINDROSE_TYPE16);

// Specify colors weights and style for the radial gridlines
$wp->SetRadialColors($axiscolors);
$wp->SetRadialWeights($axisweights);
$wp->SetRadialStyles($axisstyles);

// Add some "arbitrary" text to the center
$wp->scale->SetZeroLabel("SOx\n8%%");

// Finally add it to the graph and send back to the client
$graph->Add($wp);
$graph->Stroke();
