<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

$ydata = array(12, 17, 22, 19, 5, 15);

$graph = new Graph\Graph(220, 170);
$graph->SetScale("textlin", 3, 35);

$graph->title->Set('Manual scale, exact limits');
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$line = new Plot\LinePlot($ydata);
$graph->Add($line);

// Output graph
$graph->Stroke();
