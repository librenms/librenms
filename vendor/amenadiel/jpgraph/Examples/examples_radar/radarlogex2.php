<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_log.php');
require_once ('jpgraph/jpgraph_radar.php');

// Some data to plot
$data = array(242,58,500,12,397,810,373);

// Create the graph
$graph = new RadarGraph(200,200);

// Uncomment the following line to use anti-aliasing 
// Note: Enabling this results in a very noticable slow
// down of the image generation! And more load on your
// server. Use it wisly!!
$graph->img->SetAntiAliasing();

// Make the spider graph fill out it's bounding box
$graph->SetPlotSize(0.85);

// Use logarithmic scale (If you don't use any SetScale()
// the spider graph will default to linear scale
$graph->SetScale("log");

// Uncomment the following line if you want to supress
// minor tick marks
// $graph->yscale->ticks->SupressMinorTickMarks();

// We want the major tick marks to be black and minor
// slightly less noticable
$graph->yscale->ticks->SetMarkColor("black","darkgray");

// Set the axis title font 
$graph->axis->title->SetFont(FF_ARIAL,FS_BOLD,12);

// Use blue axis
$graph->axis->SetColor("blue");

$plot = new RadarPlot($data);
$plot->SetLineWeight(2);
$plot->SetColor('forestgreen');

// Add the plot and display the graph
$graph->Add($plot);
$graph->Stroke();
?>

