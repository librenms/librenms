<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_scatter.php';

// Make a circle with a scatterplot
$steps = 16;
for ($i = 0; $i < $steps; ++$i) {
    $a         = 2 * M_PI / $steps * $i;
    $datax[$i] = cos($a);
    $datay[$i] = sin($a);
}

$graph = new Graph\Graph(350, 230);
$graph->SetScale('linlin');
$graph->SetShadow();
$graph->SetAxisStyle(AXSTYLE_BOXOUT);

$graph->img->SetMargin(50, 50, 60, 40);

$graph->title->Set('Linked scatter plot');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->subtitle->Set('(BOXOUT Axis style)');
$graph->subtitle->SetFont(FF_FONT1, FS_NORMAL);

// 10% top and bottom grace
$graph->yscale->SetGrace(5, 5);
$graph->xscale->SetGrace(1, 1);

$sp1 = new ScatterPlot($datay, $datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor('red');
$sp1->SetColor('blue');

$sp1->mark->SetWidth(4);
$sp1->link->Show();
$sp1->link->SetStyle('dotted');

$graph->Add($sp1);
$graph->Stroke();
