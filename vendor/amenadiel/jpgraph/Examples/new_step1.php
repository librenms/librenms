<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

$datay = array(20, 10, 35, 5, 17, 35, 22);

// Setup the graph
$graph = new Graph\Graph(400, 250);
$graph->SetScale("intlin", 0, $aYMax = 50);
$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->SetBox(false);

$graph->title->Set('Step Line');
$graph->ygrid->Show(true);
$graph->xgrid->Show(false);
$graph->yaxis->HideZeroLabel();
$graph->ygrid->SetFill(true, '#FFFFFF@0.5', '#FFFFFF@0.5');
$graph->SetBackgroundGradient('blue', '#55eeff', GRAD_HOR, BGRAD_PLOT);
$graph->xaxis->SetTickLabels(array('A', 'B', 'C', 'D', 'E', 'F', 'G'));

// Create the line
$p1 = new Plot\LinePlot($datay);
$graph->Add($p1);

$p1->SetFillGradient('yellow', 'red');
$p1->SetStepStyle();
$p1->SetColor('#808000');

// Output line
$graph->Stroke();
