<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_iconplot.php");


//$datay = array(20,15,23,15,17,35,22);
$datay = array(30,25,33,25,27,45,32);
$datay2 = array(3,25,10,15,50,5,18);
$datay3 = array(10,5,10,15,5,2,1);

// Setup the graph
$graph = new Graph(400,250);
$graph->SetMargin(40,40,20,30);	
$graph->SetScale("textlin");

$graph->title->Set('Adding an icon ("tux") in the background');
$graph->title->SetFont(FF_ARIAL,FS_NORMAL,12);

//$graph->SetBackgroundGradient('red','blue');

$graph->xaxis->SetPos('min');

$p1 = new LinePlot($datay);
$p1->SetColor("blue");
$p1->SetFillGradient('yellow@0.4','red@0.4');

$p2 = new LinePlot($datay2);
$p2->SetColor("black");
$p2->SetFillGradient('green@0.4','white');

$p3 = new LinePlot($datay3);
$p3->SetColor("blue");
$p3->SetFillGradient('navy@0.4','white@0.4');

$graph->Add($p1);
$graph->Add($p2);
$graph->Add($p3);

$icon = new IconPlot('penguin.png',0.2,0.3,1,30);
$icon->SetAnchor('center','center');
$graph->Add($icon);

// Output line
$graph->Stroke();

?>


