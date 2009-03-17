<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_error.php");

$errdatay = array(11,9,2,4,19,26,13,19,7,12);

// Create the graph. These two calls are always required
$graph = new Graph(300,200,"auto");	
$graph->SetScale("textlin");

$graph->img->SetMargin(40,30,20,40);
$graph->SetShadow();

// Create the linear plot
$errplot=new ErrorLinePlot($errdatay);
$errplot->SetColor("red");
$errplot->SetWeight(2);
$errplot->SetCenter();
$errplot->line->SetWeight(2);
$errplot->line->SetColor("blue");

// Add the plot to the graph
$graph->Add($errplot);

$graph->title->Set("Linear error plot");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$datax = $gDateLocale->GetShortMonth();
$graph->xaxis->SetTickLabels($datax);


// Display the graph
$graph->Stroke();
?>