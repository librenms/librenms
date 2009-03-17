<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$ydata = array(12,17,22,19,5,15);

$graph = new Graph(270,170);
$graph->SetMargin(30,90,30,30);
$graph->SetScale("textlin");

$graph->img->SetAngle(45);
$graph->img->SetCenter(floor(270/2),floor(170/2));

$line = new LinePlot($ydata);
$line->SetLegend('2002');
$line->SetColor('darkred');
$line->SetWeight(2);
$graph->Add($line);

// Output graph
$graph->Stroke();

?>


