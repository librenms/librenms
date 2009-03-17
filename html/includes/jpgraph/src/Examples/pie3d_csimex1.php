<?php
include_once ("../jpgraph.php");
include_once ("../jpgraph_pie.php");
include_once ("../jpgraph_pie3d.php");

//$gJpgBrandTiming=true;

// Some data
$data = array(40,21,17,27,23);

// Create the Pie Graph. 
$graph = new PieGraph(400,200,'auto');
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("3D Pie Client side image map");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create
$p1 = new PiePlot3D($data);
$p1->SetLegends(array("Jan (%d)","Feb","Mar","Apr","May","Jun","Jul"));
$targ=array("pie3d_csimex1.php?v=1","pie3d_csimex1.php?v=2","pie3d_csimex1.php?v=3",
			"pie3d_csimex1.php?v=4","pie3d_csimex1.php?v=5","pie3d_csimex1.php?v=6");
$alts=array("val=%d","val=%d","val=%d","val=%d","val=%d","val=%d");
$p1->SetCSIMTargets($targ,$alts);

// Use absolute labels
$p1->SetLabelType(1);
$p1->value->SetFormat("%d kr");

// Move the pie slightly to the left
$p1->SetCenter(0.4,0.5);

$graph->Add($p1);


// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->StrokeCSIM();

?>


