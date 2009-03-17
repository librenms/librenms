<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");
include ("../jpgraph_pie3d.php");

// Some data
$data = array(20,27,45,75,90);

// Create the Pie Graph.
$graph = new PieGraph(350,200,"auto");
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Example 5 3D Pie plot");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,18); 
$graph->title->SetColor("darkblue");
$graph->legend->Pos(0.1,0.2);

// Create 3D pie plot
$p1 = new PiePlot3d($data);
$p1->SetTheme("sand");
$p1->SetCenter(0.4);
$p1->SetSize(80);

// Adjust projection angle
$p1->SetAngle(45);

// Adjsut angle for first slice
$p1->SetStartAngle(45);

// Display the slice values
$p1->value->SetFont(FF_ARIAL,FS_BOLD,11);
$p1->value->SetColor("navy");

// Add colored edges to the 3D pie
// NOTE: You can't have exploded slices with edges!
$p1->SetEdge("navy");

$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct"));

$graph->Add($p1);
$graph->Stroke();

?>


