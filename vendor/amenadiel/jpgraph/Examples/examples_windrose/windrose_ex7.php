<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$data = array(
    2 => array(1, 15, 7.5, 2),
    5 => array(1, 1, 1.5, 2),
    7 => array(1, 2, 10, 3, 2),
    9 => array(2, 3, 1, 3, 1, 2),
);

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 450);
$graph->title->Set('Windrose example 7');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 14);
$graph->title->SetColor('navy');

// Create the free windrose plot.
$wp = new Plot\WindrosePlot($data);
$wp->SetType(WINDROSE_TYPE16);

// Add some "arbitrary" text to the center
$wp->scale->SetZeroLabel("SOx\n8%%");

// Localize the compass direction labels into Swedish
// Note: The labels for data must now also match the exact
// string for the compass directions.
$se_CompassLbl = array('O', 'ONO', 'NO', 'NNO', 'N', 'NNV', 'NV', 'VNV',
    'V', 'VSV', 'SV', 'SSV', 'S', 'SSO', 'SO', 'OSO');
$wp->SetCompassLabels($se_CompassLbl);

// Localize the "Calm" text into Swedish and make the circle
// slightly bigger than default
$se_calmtext = 'Lugnt';
$wp->legend->SetCircleText($se_calmtext);
$wp->legend->SetCircleRadius(20);

// Adjust the displayed ranges
$ranges = array(1, 3, 5, 8, 12, 19, 29);
$wp->SetRanges($ranges);
//$wp->SetAntiAlias(true);

// Set the scale to always have max value of 30 with a step
// size of 12.
$wp->scale->Set(30, 12);

// Finally add it to the graph and send back to client
$graph->Add($wp);
$graph->Stroke();
