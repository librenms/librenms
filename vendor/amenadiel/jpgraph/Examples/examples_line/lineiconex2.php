<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_flags.php';
require_once 'jpgraph/jpgraph_iconplot.php';

$datay = array(30, 25, 33, 25, 27, 45, 32);

// Setup the graph
$graph = new Graph\Graph(400, 250);
$graph->SetMargin(40, 40, 20, 30);
$graph->SetScale("textlin");

$graph->title->Set('Adding a country flag as a an icon');

$p1 = new Plot\LinePlot($datay);
$p1->SetColor("blue");
$p1->SetFillGradient('yellow@0.4', 'red@0.4');

$graph->Add($p1);

$icon = new IconPlot();
$icon->SetCountryFlag('iceland', 50, 30, 1.5, 40, 3);
$icon->SetAnchor('left', 'top');
$graph->Add($icon);

// Output line
$graph->Stroke();
