<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(12,8,19,3,10,5);

// Create the graph. These two calls are always required
$graph = new Graph(260,100,"auto");	
$graph->SetScale("linlin");

// Add a drop shadow
$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(40,30,20,40);

// Create a bar pot
$bplot = new BarPlot($datay);

// Adjust fill color
$bplot->SetFillColor('orange');
$graph->Add($bplot);

// Setup the titles
$graph->title->Set("Bar graph, linear scale");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
$graph->Stroke();
?>
