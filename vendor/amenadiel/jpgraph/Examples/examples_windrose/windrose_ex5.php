<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Data
$data = array(
    0 => array(1, 1, 2.5, 4),
    1 => array(3, 4, 1, 4),
    3 => array(2, 7, 4, 4, 3),
    5 => array(2, 7, 1, 2));

// Text to be added.
$txt    = array();
$txt[0] = "It is possible to add arbitrary,multi line, text to a graph. ";
$txt[0] .= "Such a paragraph can have it's text be left, right or center ";
$txt[0] .= "aligned.";
$txt[1] = "This is an example of a right aligned paragraph.";
$txt[2] = "Finally we can show a center aligned paragraph without box.";

// We store the layout for each of the text boxes in an array
// to keep the code clean
$txtlayout = array(
    array(0.97, 0.15, 25, 'left', 'black', 'lightblue'),
    array(0.97, 0.4, 20, 'right', 'black', 'lightblue'),
    array(0.97, 0.7, 20, 'center', 'darkred', false, FF_COMIC, FS_NORMAL, 12),
);

// Range colors to be used
$rangeColors = array('silver', 'khaki', 'orange', 'brown', 'blue', 'navy', 'maroon', 'red');

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(570, 430);
$graph->title->Set('Windrose example 5');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);
$graph->title->SetColor('navy');

// Setup graph background color
$graph->SetColor('darkgreen@0.7');

// Setup all the defined text boxes
$n = count($txt);
for ($i = 0; $i < $n; ++$i) {
    $txtbox[$i] = new Text($txt[$i]);
    $txtbox[$i]->SetPos($txtlayout[$i][0], $txtlayout[$i][1], 'right');
    $txtbox[$i]->SetWordwrap($txtlayout[$i][2]);
    $txtbox[$i]->SetParagraphAlign($txtlayout[$i][3]);
    $txtbox[$i]->SetColor($txtlayout[$i][4]);
    $txtbox[$i]->SetBox($txtlayout[$i][5]);
    if (count($txtlayout[$i]) > 6) {
        $txtbox[$i]->SetFont($txtlayout[$i][6], $txtlayout[$i][7], $txtlayout[$i][8]);
    }
}
$graph->Add($txtbox);

// Create the windrose plot.
$wp = new Plot\WindrosePlot($data);

// Set background color for plot area
$wp->SetColor('lightyellow');

// Add a box around the plot
$wp->SetBox();

// Setup the colors for the ranges
$wp->SetRangeColors($rangeColors);

// Adjust the font and font color for scale labels
$wp->scale->SetFont(FF_ARIAL, FS_NORMAL, 9);
$wp->scale->SetFontColor('navy');

// Set the diameter and position for plot
$wp->SetSize(190);
$wp->SetPos(0.35, 0.53);

$wp->SetZCircleSize(0.2);

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

// Add plot and send back to client
$graph->Add($wp);
$graph->Stroke();
