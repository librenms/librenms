<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

$data = array(40,60,31,35);

// A new pie graph
$graph = new PieGraph(250,200,"auto");
$graph->SetShadow();

// Title setup
$graph->title->Set("Exploding all slices");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Setup the pie plot
$p1 = new PiePlot($data);

// Adjust size and position of plot
$p1->SetSize(0.35);
$p1->SetCenter(0.5,0.52);

// Setup slice labels and move them into the plot
$p1->value->SetFont(FF_FONT1,FS_BOLD);
$p1->value->SetColor("darkred");
$p1->SetLabelPos(0.65);

// Explode all slices
$p1->ExplodeAll(10);

// Add drop shadow
$p1->SetShadow();

// Finally add the plot
$graph->Add($p1);

// ... and stroke it
$graph->Stroke();

?>
