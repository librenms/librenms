<?php // content="text/plain; charset=utf-8"

require_once '../../vendor/autoload.php';

use Amenadiel\JpGraph\Graph;

// Setup some data to use for the contour
$data = array(
    array(12, 12, 10, 10),
    array(10, 10, 8, 14),
    array(7, 7, 13, 17),
    array(4, 5, 8, 12),
    array(10, 8, 7, 8));

// create a basic graph as a container
$graph = new Graph\Graph(300, 300);
$graph->SetMargin(30, 30, 40, 30);
$graph->SetScale('intint');
$graph->SetMarginColor('white');

// Setup title of graph
$graph->title->Set('Filled contour plot');
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 12);

$graph->subtitle->Set('(With lines and labels)');
$graph->subtitle->SetFont(FF_VERDANA, FS_ITALIC, 10);

// Create a new contour plot
$cp = new FilledContourPlot($data, 7);

// Use only blue/red color schema
$cp->UseHighContrastColor(true);

// Flip visually
$cp->SetInvert();

// Fill the contours
$cp->SetFilled(true);

// Specify method to use
$cp->SetMethod('tri');

// Display the labels
$cp->ShowLabels(true, true);
$cp->SetFont(FF_ARIAL, FS_BOLD, 9);
$cp->SetFontColor('white');

// And add the plot to the graph
$graph->Add($cp);

// Send it back to the client
$graph->stroke();
