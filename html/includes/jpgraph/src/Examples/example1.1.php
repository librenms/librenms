<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
 
$ydata = array(11,11,11);

// Create the graph. 
$graph = new Graph(350,250,"auto");	
$graph->SetScale("textlin");
$graph->img->SetMargin(30,90,40,50);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->title->Set("Example 1.1 same y-values");

// Create the linear plot
$lineplot=new LinePlot($ydata);
$lineplot->SetLegend("Test 1");
$lineplot->SetColor("blue");
$lineplot->SetWeight(5);

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
