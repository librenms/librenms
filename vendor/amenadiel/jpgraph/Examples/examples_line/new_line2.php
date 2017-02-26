<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

$datay1 = array(20, 7, 16, 46);
$datay2 = array(6, 20, 10, 22);

// Setup the graph
$graph = new Graph\Graph(350, 230);
$graph->SetScale("textlin");

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->title->Set('Background Image');
$graph->SetBox(false);

$graph->yaxis->HideZeroLabel();
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);

$graph->xaxis->SetTickLabels(array('A', 'B', 'C', 'D'));
$graph->ygrid->SetFill(false);
$graph->SetBackgroundImage("tiger_bkg.png", BGIMG_FILLFRAME);

$p1 = new Plot\LinePlot($datay1);
$graph->Add($p1);

$p2 = new Plot\LinePlot($datay2);
$graph->Add($p2);

$p1->SetColor("#55bbdd");
$p1->SetLegend('Line 1');
$p1->mark->SetType(MARK_FILLEDCIRCLE, '', 1.0);
$p1->mark->SetColor('#55bbdd');
$p1->mark->SetFillColor('#55bbdd');
$p1->SetCenter();

$p2->SetColor("#aaaaaa");
$p2->SetLegend('Line 2');
$p2->mark->SetType(MARK_UTRIANGLE, '', 1.0);
$p2->mark->SetColor('#aaaaaa');
$p2->mark->SetFillColor('#aaaaaa');
$p2->value->SetMargin(14);
$p2->SetCenter();

$graph->legend->SetFrameWeight(1);
$graph->legend->SetColor('#4E4E4E', '#00A78A');
$graph->legend->SetMarkAbsSize(8);

// Output line
$graph->Stroke();
