<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data = array(
    0 => array(1, 1, 6, 4),
    1 => array(3, 8, 1, 4),
    2 => array(2, 7, 4, 4, 3),
    3 => array(2, 7, 1, 2));

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 400);

// Setup title
$graph->title->Set('Windrose example 2');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Make it have 8 compass direction
$wp->SetType(WINDROSE_TYPE4);

// Setup the weight of the laegs for the different ranges
$weights = array_fill(0, 8, 10);
$wp->SetRangeWeights($weights);

// Adjust the font and font color for scale labels
$wp->scale->SetFont(FF_TIMES, FS_NORMAL, 11);
$wp->scale->SetFontColor('navy');

// Set the diametr for the plot to 160 pixels
$wp->SetSize(160);

// Set the size of the innermost center circle to 30% of the plot size
$wp->SetZCircleSize(0.2);

// Adjust the font and font color for compass directions
$wp->SetFont(FF_ARIAL, FS_NORMAL, 12);
$wp->SetFontColor('darkgreen');

// Add and send back to browser
$graph->Add($wp);
$graph->Stroke();
