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
$graph->title->Set("Example 2 3D Pie plot");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,18); 
$graph->title->SetColor("darkblue");
$graph->legend->Pos(0.1,0.2);

// Create 3D pie plot
$p1 = new PiePlot3d($data);
$p1->SetTheme("sand");
$p1->SetCenter(0.4);
$p1->SetSize(0.4);
$p1->SetHeight(5);

// Adjust projection angle
$p1->SetAngle(45);

// You can explode several slices by specifying the explode
// distance for some slices in an array
$p1->Explode(array(0,40,0,30));

// As a shortcut you can easily explode one numbered slice with
// $p1->ExplodeSlice(3);

$p1->value->SetFont(FF_ARIAL,FS_NORMAL,10);
$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct"));

$graph->Add($p1);
$graph->Stroke();

?>


