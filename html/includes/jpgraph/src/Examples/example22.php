<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$data1y=array(12,8,19,3,10,5);
$data2y=array(8,2,11,7,14,4);

// Create the graph. These two calls are always required
$graph = new Graph(310,200,"auto");	
$graph->SetScale("textlin");
$graph->SetShadow();

$graph->img->SetMargin(40,30,20,40);

// Create the bar plots
$b1plot = new BarPlot($data1y);
$b1plot->SetFillColor("orange");
$b2plot = new BarPlot($data2y);
$b2plot->SetFillColor("blue");

// Create the grouped bar plot
$gbplot = new GroupBarPlot(array($b1plot,$b2plot));
$gbplot->SetWidth(0.9);

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->title->Set("Adjusting the width");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
$graph->Stroke();
?>
