<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$labels = array("Oct 2000","Nov 2000","Dec 2000","Jan 2001","Feb 2001","Mar 2001","Apr 2001","May 2001");
$datay = array(1.23,1.9,1.6,3.1,3.4,2.8,2.1,1.9);
$graph = new Graph(300,250,"auto");
$graph->img->SetMargin(40,40,40,80);	
$graph->img->SetAntiAliasing();
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->title->Set("Example slanted X-labels");
$graph->title->SetFont(FF_VERDANA,FS_NORMAL,14);

$graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,11);
$graph->xaxis->SetTickLabels($labels);
$graph->xaxis->SetLabelAngle(45);

$p1 = new LinePlot($datay);
$p1->mark->SetType(MARK_FILLEDCIRCLE);
$p1->mark->SetFillColor("red");
$p1->mark->SetWidth(4);
$p1->SetColor("blue");
$p1->SetCenter();
$graph->Add($p1);

$graph->Stroke();

?>


