<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

// Some data
$data = array(40,21,17,14,23);

// Create the Pie Graph. 
$graph = new PieGraph(300,200,"auto");
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Example 2 Pie plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create
$p1 = new PiePlot($data);
$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul"));
$graph->Add($p1);
$graph->Stroke();

?>


