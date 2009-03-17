<?php

include ("../jpgraph.php");
include ("../jpgraph_bar.php");

// We need some data
$datay=array(4,8,6);

// Setup the graph. 
$graph = new Graph(200,150,"auto");	
$graph->SetScale("textlin");
$graph->img->SetMargin(25,15,25,25);

$graph->title->Set('"GRAD_HOR"');
$graph->title->SetColor('darkred');

// Setup font for axis
$graph->xaxis->SetFont(FF_FONT1);
$graph->yaxis->SetFont(FF_FONT1);

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// Setup color for gradient fill style 
$bplot->SetFillGradient("navy","lightsteelblue",GRAD_HOR);

// Set color for the frame of each bar
$bplot->SetColor("navy");
$graph->Add($bplot);

// Finally send the graph to the browser
$graph->Stroke();
?>
