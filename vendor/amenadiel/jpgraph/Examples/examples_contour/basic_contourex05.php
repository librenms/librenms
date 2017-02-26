<?php // content="text/plain; charset=utf-8"
// Basic contour plot example

require_once '../../vendor/autoload.php';

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$data = array(
    array(0.5, 1.1, 1.5, 1, 2.0, 3, 3, 2, 1, 0.1),
    array(1.0, 1.5, 3.0, 5, 6.0, 2, 1, 1.2, 1, 4),
    array(0.9, 2.0, 2.1, 3, 6.0, 7, 3, 2, 1, 1.4),
    array(1.0, 1.5, 3.0, 4, 6.0, 5, 2, 1.5, 1, 2),
    array(0.8, 2.0, 3.0, 3, 4.0, 4, 3, 2.4, 2, 3),
    array(0.6, 1.1, 1.5, 1, 4.0, 3.5, 3, 2, 3, 4),
    array(1.0, 1.5, 3.0, 5, 6.0, 2, 1, 1.2, 2.7, 4),
    array(0.8, 2.0, 3.0, 3, 5.5, 6, 3, 2, 1, 1.4),
    array(1.0, 1.5, 3.0, 4, 6.0, 5, 2, 1, 0.5, 0.2));

// Basic contour graph
$graph = new Graph\Graph(350, 250);
$graph->SetScale('intint');

// Show axis on all sides
$graph->SetAxisStyle(AXSTYLE_BOXOUT);

// Adjust the margins to fit the margin
$graph->SetMargin(30, 100, 40, 30);

// Setup
$graph->title->Set('Basic contour plot with multiple axis');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);

// A simple contour plot with default arguments (e.g. 10 isobar lines)
$cp = new Plot\ContourPlot($data);

// Flip the data around its center line
$cp->SetInvert();

// Display the legend
$cp->ShowLegend();

$graph->Add($cp);

// ... and send the graph back to the browser
$graph->Stroke();
