<?php // content="text/plain; charset=utf-8"
// Contour plot example

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

// Setup a basic graph context with some generous margins to be able
// to fit the legend
$graph = new Graph\Graph(500, 380);
$graph->SetMargin(40, 140, 60, 40);

// Enable antialias. Note with antiaaliasing only line weight=1 is supported.
$graph->img->SetAntiAliasing();

$graph->title->Set("Example of contour plot");
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);

// For contour plots it is custom to use a box style ofr the axis
$graph->legend->SetPos(0.05, 0.5, 'right', 'center');
$graph->SetScale('intint');
$graph->SetAxisStyle(AXSTYLE_BOXOUT);
$graph->xgrid->Show();
$graph->ygrid->Show();

// A simple contour plot with 19 isobars and flipped vertical range
$cp = new Plot\ContourPlot($data, 10, true);

// Display the legend
$cp->ShowLegend();

// Invert the legend to th lowest isobar is on top
$cp->Invertlegend();
$graph->Add($cp);

// ... and send the graph back to the browser
$graph->Stroke();
