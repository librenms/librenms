<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_polar.php');

//$data = array(22,12,27,40,80,48,120,40,142,27,170,12);

$data = array(
0,0,10,2,30,25,40,60,
50,110,60,140,70,170,80,190,
85,195,90,200,95,195,100,190,
110,170,120,140,130,110,140,60,
150,25,170,2,180,0);

//$data2 = array(0,0,50,2,60,30,65,90,60,120,50,150,20,170,0,180);

$data2 = array(0,0,34,56,90,90,170,65,220,90,270,120,300,60,355,10);

$graph = new PolarGraph(350,350);
$graph->SetScale('lin',150);

$graph->SetMarginColor('#FFE6C0');
$graph->SetType(POLAR_360);
$graph->SetClockwise(true);
$graph->Set90AndMargin(40,40,50,40);

$graph->SetBox(true);
$graph->SetFrame(false);
$graph->axis->ShowGrid(true,false,true);
$graph->axis->SetGridColor('gray','gray','gray');

$graph->axis->SetFont(FF_ARIAL,FS_NORMAL,8);
$graph->axis->SetTitle('X-Axis','center');

$graph->axis->SetColor('black','black','darkred');
$graph->axis->SetAngleFont(FF_ARIAL,FS_NORMAL,8);

$graph->title->Set('Clockwise polar plot (rotated)');
$graph->title->SetFont(FF_COMIC,FS_NORMAL,16);
$graph->title->SetColor('navy');



$p = new PolarPlot($data);
$p->SetFillColor('lightblue@0.5');
$graph->Add($p);

//$p2 = new PolarPlot($data2);
//$p2->SetFillColor('red@0.5');
//$graph->Add($p2);

$graph->Stroke();

?>
