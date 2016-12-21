<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
// Data can be specified using both ordinal index of the axis
// as well as the direction label
$data[0] = array(
    0 => array(1, 1, 2.5, 4),
    1 => array(3, 4, 1, 4),
    3 => array(2, 7, 4, 4, 3),
    5 => array(2, 7, 1, 2));

$data[1] = array(
    "n" => array(1, 1, 2.5, 4),
    "ssw" => array(3, 4, 1, 4),
    "se" => array(2, 7, 4, 4, 3));

// Store the position and size data for each plot in an
// array to make it easier to create multiple plots.
// The format choosen for the layout data is
// (type,x-pos,y-pos,size, z-circle size)
$layout = array(
    array(WINDROSE_TYPE8, 0.25, 0.55, 0.4, 0.25),
    array(WINDROSE_TYPE16, 0.75, 0.55, 0.4, 0.25));

$legendtxt = array('(m/s) Station 7', '(m/s) Station 12');

// First create a new windrose graph with a dropshadow
$graph = new Graph\WindroseGraph(600, 350);
$graph->SetShadow('darkgray');

// Setup titles
$graph->title->Set('Windrose example 3');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');
$graph->subtitle->Set('(Multiple plots in the same graph)');
$graph->subtitle->SetFont(FF_VERDANA, FS_NORMAL, 9);
$graph->subtitle->SetColor('navy');

// Create the two windrose plots.
for ($i = 0; $i < count($data); ++$i) {
    $wp[$i] = new Plot\WindrosePlot($data[$i]);

    // Make it have 8 compass direction
    $wp[$i]->SetType($layout[$i][0]);

    // Adjust the font and font color for scale labels
    $wp[$i]->scale->SetFont(FF_TIMES, FS_NORMAL, 10);
    $wp[$i]->scale->SetFontColor('navy');

    // Set the position of the plot
    $wp[$i]->SetPos($layout[$i][1], $layout[$i][2]);

    // Set the diameter for the plot to 30% of the width of the graph pixels
    $wp[$i]->SetSize($layout[$i][3]);

    // Set the size of the innermost center circle to 30% of the plot size
    $wp[$i]->SetZCircleSize($layout[$i][4]);

    // Adjust the font and font color for compass directions
    $wp[$i]->SetFont(FF_ARIAL, FS_NORMAL, 10);
    $wp[$i]->SetFontColor('darkgreen');

    // Add legend text
    $wp[$i]->legend->SetText($legendtxt[$i]);

    $graph->Add($wp[$i]);
}

// Send the graph to the browser
$graph->Stroke();
