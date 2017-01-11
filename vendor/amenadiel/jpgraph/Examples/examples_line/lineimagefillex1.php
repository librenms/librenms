<?php
require_once '../jpgraph.php';
require_once '../jpgraph_line.php';

$datay = array(0, 3, 5, 12, 15, 18, 22, 36, 37, 41);

// Setup the graph
$graph = new Graph\Graph(320, 200);
$graph->title->Set('Education growth');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph->SetScale('intlin');
$graph->SetMarginColor('white');
$graph->SetBox();
//$graph->img->SetAntialiasing();

$graph->SetGridDepth(DEPTH_FRONT);
$graph->ygrid->SetColor('gray@0.7');
$graph->SetBackgroundImage('classroom.jpg', BGIMG_FILLPLOT);

// Masking graph
$p1 = new Plot\LinePlot($datay);
$p1->SetFillColor('white');
$p1->SetFillFromYMax();
$p1->SetWeight(0);
$graph->Add($p1);

// Line plot
$p2 = new Plot\LinePlot($datay);
$p2->SetColor('black@0.4');
$p2->SetWeight(3);
$p2->mark->SetType(MARK_SQUARE);
$p2->mark->SetColor('orange@0.5');
$p2->mark->SetFillColor('orange@0.3');
$graph->Add($p2);

// Output line
$graph->Stroke();
