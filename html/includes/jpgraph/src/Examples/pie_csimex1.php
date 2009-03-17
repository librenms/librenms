<?php
include_once ("../jpgraph.php");
include_once ("../jpgraph_pie.php");

// Some data
$data = array(40,21,17,14,23);

// Create the Pie Graph. 
$graph = new PieGraph(300,200,'auto');
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Client side image map");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create
$p1 = new PiePlot($data);
$p1->SetCenter(0.4,0.5);

$p1->SetLegends(array("Jan","Feb","Mar","Apr","May","Jun","Jul"));
$targ=array("pie_csimex1.php#1","pie_csimex1.php#2","pie_csimex1.php#3",
"pie_csimex1.php#4","pie_csimex1.php#5","pie_csimex1.php#6");
$alts=array("val=%d","val=%d","val=%d","val=%d","val=%d","val=%d");
$p1->SetCSIMTargets($targ,$alts);

$graph->Add($p1);


// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->StrokeCSIM();

?>


