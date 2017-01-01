<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

$datay = array(0.2980, 0.3039, 0.3020, 0.3027, 0.3015);

$graph = new Graph\Graph(300, 200);
$graph->img->SetMargin(40, 40, 40, 40);

$graph->img->SetAntiAliasing();
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->title->Set("Example of 10% top/bottom grace");
$graph->title->SetFont(FF_FONT1, FS_BOLD);

// Add 10% grace to top and bottom of plot
$graph->yscale->SetGrace(10, 10);

$p1 = new Plot\LinePlot($datay);
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);
$p1->SetColor("blue");
$p1->SetCenter();
$graph->Add($p1);

$graph->Stroke();
