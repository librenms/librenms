<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

// Some data
$datay = array(25,29,29,39,55);

$graph = new Graph(400,200,'auto');
$graph->img->SetMargin(40,40,40,20);

$graph->SetScale("linlin");
$graph->SetShadow();
$graph->title->Set("Top X-axis");

// Start at 0
$graph->yscale->SetAutoMin(0);

// Add some air around the Y-scale
$graph->yscale->SetGrace(100);

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Adjust the X-axis
$graph->xaxis->SetPos("max");
$graph->xaxis->SetLabelSide(SIDE_UP);
$graph->xaxis->SetTickSide(SIDE_DOWN);

// Create the line plot
$p1 = new LinePlot($datay);
$p1->SetColor("blue");

// Specify marks for the line plots
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);

// Show values
$p1->value->Show();

// Add lineplot to graph
$graph->Add($p1);

// Output line
$graph->Stroke();

?>


