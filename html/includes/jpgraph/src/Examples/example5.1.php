<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$ydata = array(11,3,8,12,5,1,9,13,5,7);
$y2data = array(354,200,265,99,111,91,198,225,293,251);

// Create the graph. These two calls are always required
$graph = new Graph(300,200,"auto");	
$graph->img->SetMargin(40,40,20,40);
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

// Create the linear plot
$lineplot=new LinePlot($ydata);
$lineplot2=new LinePlot($y2data);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->AddY2($lineplot2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);
$graph->y2axis->SetColor("orange");

$graph->title->Set("Example 5");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$lineplot->SetColor("blue");
$lineplot->SetWeight(2);

$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

$graph->yaxis->SetColor("blue");

$lineplot->SetLegend("Plot 1");
$lineplot2->SetLegend("Plot 2");

// Display the graph
$graph->Stroke();
?>
