<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';

$n = 8;
for ($i = 0; $i < $n; ++$i) {
    $datay[$i] = rand(1, 10);
    $datay2[$i] = rand(10, 55);
    $datay3[$i] = rand(200, 600);
    $datay4[$i] = rand(500, 800);
}

// Setup the graph
$graph = new Graph\Graph(450, 250);
$graph->SetMargin(40, 150, 40, 30);
$graph->SetMarginColor('white');

$graph->SetScale('intlin');
$graph->title->Set('Using multiple Y-axis');
$graph->title->SetFont(FF_ARIAL, FS_NORMAL, 14);

$graph->SetYScale(0, 'lin');
$graph->SetYScale(1, 'lin');
$graph->SetYScale(2, 'lin');

$p1 = new Plot\LinePlot($datay);
$graph->Add($p1);

$p2 = new Plot\LinePlot($datay2);
$p2->SetColor('teal');
$graph->AddY(0, $p2);
$graph->ynaxis[0]->SetColor('teal');

$p3 = new Plot\LinePlot($datay3);
$p3->SetColor('red');
$graph->AddY(1, $p3);
$graph->ynaxis[1]->SetColor('red');

$p4 = new Plot\LinePlot($datay4);
$p4->SetColor('blue');
$graph->AddY(2, $p4);
$graph->ynaxis[2]->SetColor('blue');

// Output line
$graph->Stroke();
