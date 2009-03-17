<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$ydata = array(11,3,8,10,5,1,9,13,5,7);

// Create the graph. These two calls are always required
$graph = new Graph(300,200,"auto");	
$graph->SetScale("textlin");

// Setup margin and titles
$graph->img->SetMargin(40,20,20,40);
$graph->title->Set("Example 2");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

// Create the linear plot
$lineplot=new LinePlot($ydata);

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();
?>
