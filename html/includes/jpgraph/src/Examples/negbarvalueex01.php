<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(12,0,-19,-7,17,-6);

// Create the graph. 
$graph = new Graph(400,300,"auto");	
$graph->img->SetMargin(60,30,40,40);
$graph->SetScale("textlin");
$graph->SetShadow();

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("orange");

// DIsplay value at top of each bar
$bplot->value->Show();
$bplot->SetShadow();

$graph->Add($bplot);

// Position the scale at the min of the other axis
$graph->xaxis->SetPos("min");

// Add 10% more space at top and bottom of graph
$graph->yscale->SetGrace(10,10);

$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
$graph->title->Set("Example of bar plot with absolute labels");

$graph->yaxis->title->SetFont(FF_ARIAL,FS_NORMAL,16);


$graph->Stroke();
?>
