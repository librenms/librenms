<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
// Data can be specified using both ordinal index of the axis
// as well as the direction label.
$data = array(
    '45.9' => array(3, 2, 1, 2, 2),
    355 => array(1, 1, 1.5, 2),
    180 => array(1, 1, 1.5, 2),
    150 => array(1, 2, 1, 3),
    'S' => array(2, 3, 5, 1),
);

// Add some labels for  afew of the directions
$labels = array(355 => "At\nHome base", 180 => "Probe\n123", 150 => "Power\nplant");

// Define the color,weight and style of some individual radial grid lines.
$axiscolors = array(355 => "red");
$axisweights = array(355 => 8);
$axisstyles = array(355 => 'solid', 150 => 'solid');

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 500);
$graph->title->Set('Windrose example 8');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);
$graph->title->SetColor('navy');

// Create the free windrose plot.
$wp = new Plot\WindrosePlot($data);
$wp->SetType(WINDROSE_TYPEFREE);

// Specify colors weights and style for the radial gridlines
$wp->SetRadialColors($axiscolors);
$wp->SetRadialWeights($axisweights);
$wp->SetRadialStyles($axisstyles);

// Add a few labels
$wp->SetLabels($labels);

// Add some "arbitrary" text to the center
$wp->scale->SetZeroLabel("SOx\n8%%");

// Finally add it to the graph and send back to client
$graph->Add($wp);
$graph->Stroke();
