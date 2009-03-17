<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_bar.php");

$l1datay = array(11,9,2,4,3,13,17);
$l2datay = array(23,12,5,19,17,10,15);

$datax=$gDateLocale->GetShortMonth();

// Create the graph. 
$graph = new Graph(400,200,"auto");	
$graph->SetScale("textlin");
$graph->SetMargin(40,130,20,40);
$graph->SetShadow();
$graph->xaxis->SetTickLabels($datax);

// Create the linear error plot
$l1plot=new LinePlot($l1datay);
$l1plot->SetColor("red");
$l1plot->SetWeight(2);
$l1plot->SetLegend("Prediction");

//Center the line plot in the center of the bars
$l1plot->SetBarCenter();


// Create the bar plot
$bplot = new BarPlot($l2datay);
$bplot->SetFillColor("orange");
$bplot->SetLegend("Result");

// Add the plots to t'he graph
$graph->Add($bplot);
$graph->Add($l1plot);


$graph->title->Set("Adding a line plot to a bar graph v1");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);


// Display the graph
$graph->Stroke();
?>
