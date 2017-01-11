<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data = array(
    '10' => array(1, 1, 2.5, 4),
    '32.0' => array(3, 4, 1, 4),
    '120.5' => array(2, 3, 4, 4, 3, 2, 1),
    '223.2' => array(2, 4, 1, 2, 2),
    '285.7' => array(2, 2, 1, 2, 4, 2, 1, 1),
);

// Specify text for direction labels
$labels = array('120.5' => "Plant\n#1275",
    '285.7' => "Reference\n#13 Ver:2");

// Range colors to be used
$rangeColors = array('khaki', 'yellow', 'orange', 'orange:0.7', 'brown', 'darkred', 'black');

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(400, 450);

// Setup titles
$graph->title->Set('Windrose example 6');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

$graph->subtitle->Set('(Free type plot)');
$graph->subtitle->SetFont(FF_VERDANA, FS_ITALIC, 10);
$graph->subtitle->SetColor('navy');

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Setup a free plot
$wp->SetType(WINDROSE_TYPEFREE);

// Setup labels
$wp->SetLabels($labels);
$wp->SetLabelPosition(LBLPOSITION_CENTER);
$wp->SetLabelMargin(30);

// Setup the colors for the ranges
$wp->SetRangeColors($rangeColors);

// Adjust the font and font color for scale labels
$wp->scale->SetFont(FF_ARIAL, FS_NORMAL, 9);

// Set the diameter and position for plot
$wp->SetSize(230);
$wp->SetZCircleSize(30);

// Adjust the font and font color for compass directions
$wp->SetFont(FF_ARIAL, FS_NORMAL, 10);
$wp->SetFontColor('darkgreen');

// Adjust grid colors
$wp->SetGridColor('darkgreen@0.7', 'blue');

// Add (m/s) text to legend
$wp->legend->SetText('(m/s)');

// Display legend values with no decimals
$wp->legend->SetFormat('%d');

// Add plot to graph and send back to the client
$graph->Add($wp);
$graph->Stroke();
