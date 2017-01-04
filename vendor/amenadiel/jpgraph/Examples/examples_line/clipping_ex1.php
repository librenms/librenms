<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

$ydata = array(11, 3, 8, 12, 5, 1, 9, 13, 5, 7);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(300, 250);
$graph->SetScale('intlin', 0, 10);
$graph->SetMargin(30, 20, 70, 40);
$graph->SetMarginColor(array(177, 191, 174));

$graph->SetClipping(false);

$graph->xaxis->SetFont(FF_FONT1, FS_BOLD);

$graph->ygrid->SetLineStyle('dashed');

$graph->title->Set("Manual scale");
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph->title->SetColor('white');
$graph->subtitle->Set("(No clipping)");
$graph->subtitle->SetColor('white');
$graph->subtitle->SetFont(FF_ARIAL, FS_BOLD, 10);

// Create the linear plot
$lineplot = new Plot\LinePlot($ydata);
$lineplot->SetColor("red");
$lineplot->SetWeight(2);

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
