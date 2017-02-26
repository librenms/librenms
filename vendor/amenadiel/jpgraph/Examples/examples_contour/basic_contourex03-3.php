<?php // content="text/plain; charset=utf-8"
// Basic contour plot example

require_once '../../vendor/autoload.php';

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$data = array(
    array(12, 7, 3, 15),
    array(18, 5, 1, 9),
    array(13, 9, 5, 12),
    array(5, 3, 8, 9),
    array(1, 8, 5, 7));

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
$cp = new Plot\ContourPlot($data, 10, 3);

// Display the legend
$cp->ShowLegend();

$graph->Add($cp);

// ... and send the graph back to the browser
$graph->Stroke();
