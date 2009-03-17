<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

// Some data
$data = array(40,21,17,14,23);

// Create the Pie Graph.
$graph = new PieGraph(350,300,"auto");
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Multiple - Pie plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create plots
$size=0.13;
$p1 = new PiePlot($data);
$p1->SetLegends(array("Jan","Feb","Mar","Apr","May"));
$p1->SetSize($size);
$p1->SetCenter(0.25,0.32);
$p1->value->SetFont(FF_FONT0);
$p1->title->Set("2001");

$p2 = new PiePlot($data);
$p2->SetSize($size);
$p2->SetCenter(0.65,0.32);
$p2->value->SetFont(FF_FONT0);
$p2->title->Set("2002");

$p3 = new PiePlot($data);
$p3->SetSize($size);
$p3->SetCenter(0.25,0.75);
$p3->value->SetFont(FF_FONT0);
$p3->title->Set("2003");

$p4 = new PiePlot($data);
$p4->SetSize($size);
$p4->SetCenter(0.65,0.75);
$p4->value->SetFont(FF_FONT0);
$p4->title->Set("2004");

$graph->Add($p1);
$graph->Add($p2);
$graph->Add($p3);
$graph->Add($p4);

$graph->Stroke();

?>



