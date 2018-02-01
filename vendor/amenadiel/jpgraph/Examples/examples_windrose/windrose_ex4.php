<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data = array(
    0 => array(1, 1, 2.5, 4),
    1 => array(3, 4, 1, 4),
    3 => array(2, 7, 4, 4, 3),
    5 => array(2, 7, 1, 2));

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 400);

// Setup title
$graph->title->Set('Windrose example 4');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Adjust the font and font color for scale labels
$wp->scale->SetFont(FF_TIMES, FS_NORMAL, 11);
$wp->scale->SetFontColor('navy');

// Set the diameter and position for plot
$wp->SetSize(190);

// Set the size of the innermost center circle to 40% of the plot size
// Note that we can have the automatic "Zero" sum appear in our custom text
$wp->SetZCircleSize(0.38);
$wp->scale->SetZeroLabel("Station 12\n(Calm %d%%)");

// Adjust color and font for center circle text
$wp->scale->SetZFont(FF_ARIAL, FS_NORMAL, 9);
$wp->scale->SetZFontColor('darkgreen');

// Adjust the font and font color for compass directions
$wp->SetFont(FF_ARIAL, FS_NORMAL, 10);
$wp->SetFontColor('darkgreen');

// Adjust the margin to the compass directions
$wp->SetLabelMargin(50);

// Adjust grid colors
$wp->SetGridColor('silver', 'blue');

// Add (m/s) text to legend
$wp->legend->SetText('(m/s)');
$wp->legend->SetMargin(20, 5);

// Add and send back to client
$graph->Add($wp);
$graph->Stroke();
