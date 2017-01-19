<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_scatter.php';

$datax = array(3.5, 3.7, 3, 4, 6.2, 6, 3.5, 8, 14, 8, 11.1, 13.7);
$datay = array(20, 22, 12, 13, 17, 20, 16, 19, 30, 31, 40, 43);

$graph = new Graph\Graph(300, 200);
$graph->SetScale("linlin");

$graph->img->SetMargin(40, 40, 40, 40);
$graph->SetShadow();

$graph->title->Set("A simple scatter plot");
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$sp1 = new ScatterPlot($datay, $datax);

$graph->Add($sp1);
$graph->Stroke();
