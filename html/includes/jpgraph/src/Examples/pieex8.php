<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

$data = array(40,60,31,35);

// A new pie graph
$graph = new PieGraph(250,200,"auto");
$graph->SetShadow();

// Title setup
$graph->title->Set("Adjusting the label pos");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Setup the pie plot
$p1 = new PiePlot($data);

// Adjust size and position of plot
$p1->SetSize(0.4);
$p1->SetCenter(0.5,0.52);

// Setup slice labels and move them into the plot
$p1->value->SetFont(FF_FONT1,FS_BOLD);
$p1->value->SetColor("darkred");
$p1->SetLabelPos(0.6);

// Finally add the plot
$graph->Add($p1);

// ... and stroke it
$graph->Stroke();

?>
