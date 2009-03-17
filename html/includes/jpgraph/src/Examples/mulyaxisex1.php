<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$n = 8;
for($i=0; $i < $n; ++$i ) {
    $datay[$i] = rand(1,10);
    $datay2[$i] = rand(10,55);
    $datay3[$i] = rand(200,600);
    $datay4[$i] = rand(521,655);
}

$datay4[0] = 520;
$datay4[7] = 660;

// Setup the graph
$graph = new Graph(450,250);
$graph->SetMargin(40,150,40,30);
$graph->SetMarginColor('white');

$graph->SetScale('intlin');
$graph->title->Set('Using multiple Y-axis');
$graph->title->SetFont(FF_ARIAL,FS_NORMAL,14);

$graph->SetYScale(0,'lin');
$graph->SetYScale(1,'lin');
$graph->SetYScale(2,'lin');

$p1 = new LinePlot($datay);
$graph->Add($p1);

$p2 = new LinePlot($datay2);
$p2->SetColor('teal');
$graph->AddY(0,$p2);
$graph->ynaxis[0]->SetColor('teal');

$p3 = new LinePlot($datay3);
$p3->SetColor('red');
$graph->AddY(1,$p3);
$graph->ynaxis[1]->SetColor('red');

$p4 = new LinePlot($datay4);
$p4->SetColor('blue');
$graph->AddY(2,$p4);
$graph->ynaxis[2]->SetColor('blue');

// Output line
$graph->Stroke();
?>


