<?php
// Example for use of JpGraph, 
// ljp, 01/03/01 19:44
include ("../jpgraph.php");
include ("../jpgraph_bar.php");
include ("../jpgraph_line.php");

// We need some data
$datay=array(0.3031,0.3044,0.3049,0.3040,0.3024,0.3047);

// Setup the graph. 
$graph = new Graph(400,200,"auto");	
$graph->img->SetMargin(60,30,30,40);
$graph->SetScale("textlin");
$graph->SetMarginColor("teal");
$graph->SetShadow();

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// This is how you make the bar graph start from something other than 0
$bplot->SetYMin(0.302);

// Setup color for gradient fill style 
$tcol=array(100,100,255);
$fcol=array(255,100,100);
$bplot->SetFillGradient($fcol,$tcol,GRAD_VERT);
$bplot->SetFillColor("orange");
$graph->Add($bplot);

// Set up the title for the graph
$graph->title->Set("Bargraph which doesn't start from y=0");
$graph->title->SetColor("yellow");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);

// Setup color for axis and labels
$graph->xaxis->SetColor("black","white");
$graph->yaxis->SetColor("black","white");

// Setup font for axis
$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);

// Setup X-axis title (color & font)
$graph->xaxis->title->Set("X-axis");
$graph->xaxis->title->SetColor("white");
$graph->xaxis->title->SetFont(FF_VERDANA,FS_BOLD,10);

// Finally send the graph to the browser
$graph->Stroke();
?>
