<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

// Some data
$ydata = array(11, 3, 8, 12, 5, 1, 9, 13, 5, 7);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(200, 150);
$graph->SetScale("textlin");
$graph->SetMargin(25, 10, 30, 30);

$graph->title->SetFont(FF_ARIAL, FS_BOLD, 12);
$graph->title->Set('The Title');
$graph->subtitle->SetFont(FF_ARIAL, FS_BOLD, 10);
$graph->subtitle->Set('The Subtitle');
$graph->subsubtitle->SetFont(FF_ARIAL, FS_ITALIC, 9);
$graph->subsubtitle->Set('The Subsubitle');

// Create the linear plot
$lineplot = new Plot\LinePlot($ydata);
$lineplot->SetColor("blue");

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
