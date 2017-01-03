<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_scatter.php';

$datay1 = array(33, 20, 24, 5, 38, 24, 22);
$datay2 = array(9, 7, 10, 25, 10, 8, 4);

// Setup the graph
$graph = new Graph\Graph(300, 250);

$graph->SetScale("textlin", 0, 50);

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->title->Set("Line Plots with Markers");

$graph->SetBox(false);
$graph->ygrid->SetFill(false);
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
$graph->yaxis->HideZeroLabel();

$graph->xaxis->SetTickLabels(array('A', 'B', 'C', 'D', 'E', 'F', 'G'));
// Create the plot
$p1 = new Plot\LinePlot($datay1);
$graph->Add($p1);

$p2 = new Plot\LinePlot($datay2);
$graph->Add($p2);

// Use an image of favourite car as marker
$p1->mark->SetType(MARK_IMG, 'new1.gif', 0.8);
$p1->SetColor('#aadddd');
$p1->value->SetFormat('%d');
$p1->value->Show();
$p1->value->SetColor('#55bbdd');

$p2->mark->SetType(MARK_IMG, 'new2.gif', 0.8);
$p2->SetColor('#ddaa99');
$p2->value->SetFormat('%d');
$p2->value->Show();
$p2->value->SetColor('#55bbdd');

$graph->Stroke();
