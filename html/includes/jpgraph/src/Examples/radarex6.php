<?php
include ("../jpgraph.php");
include ("../jpgraph_radar.php");
	
// Some data to plot
$data = array(55,80,26,31,95);
	
// Create the graph and the plot
$graph = new RadarGraph(250,200,"auto");

// Add a drop shadow to the graph
$graph->SetShadow();

// Create the titles for the axis
$titles = $gDateLocale->GetShortMonth();
$graph->SetTitles($titles);
$graph->SetColor('lightyellow');

// ADjust the position to make more room
// for the legend
$graph->SetCenter(0.4,0.5);

// Add grid lines
$graph->grid->Show();
$graph->grid->SetColor('darkred');
$graph->grid->SetLineStyle('dotted');

$plot = new RadarPlot($data);
$plot->SetFillColor('lightblue');
$plot->SetLegend("QA results");

// Add the plot and display the graph
$graph->Add($plot);
$graph->Stroke();
?>