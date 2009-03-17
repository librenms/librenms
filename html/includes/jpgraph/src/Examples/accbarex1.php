<?php

include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$data1y=array(-8,8,9,3,5,6);
$data2y=array(18,2,1,7,5,4);

// Create the graph. These two calls are always required
$graph = new Graph(500,400,"auto"); 
$graph->SetScale("textlin");

$graph->SetShadow();
$graph->img->SetMargin(40,30,20,40);

// Create the bar plots
$b1plot = new BarPlot($data1y);
$b1plot->SetFillColor("orange");
$b1plot->value->Show();
$b2plot = new BarPlot($data2y);
$b2plot->SetFillColor("blue");
$b2plot->value->Show();

// Create the grouped bar plot
$gbplot = new AccBarPlot(array($b1plot,$b2plot));

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->title->Set("Accumulated bar plots");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
$graph->Stroke();
?>