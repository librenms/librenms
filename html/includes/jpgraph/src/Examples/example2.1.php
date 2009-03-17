<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$ydata = array(11,-3,-8,7,5,-1,9,13,5,-7);

// Create the graph. These two calls are always required
$graph = new Graph(300,200,"auto");	
$graph->SetScale("textlin");

// Create the linear plot
$lineplot=new LinePlot($ydata);

$lineplot->value->Show();
$lineplot->value->SetColor("red");
$lineplot->value->SetFont(FF_FONT1,FS_BOLD);


// Add the plot to the graph
$graph->Add($lineplot);

$graph->img->SetMargin(40,20,20,40);
$graph->title->Set("Example 2.1");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");


// Display the graph
$graph->Stroke();
?>
