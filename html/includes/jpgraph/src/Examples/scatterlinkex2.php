<?php
include ("../jpgraph.php");
include ("../jpgraph_scatter.php");


// Make a circle with a scatterplot
$steps=16;
for($i=0; $i<$steps; ++$i) {
	$a=2*M_PI/$steps*$i;
	$datax[$i]=cos($a);
	$datay[$i]=sin($a);
}


$graph = new Graph(300,200,"auto");
$graph->SetScale("linlin");

$graph->img->SetMargin(40,40,40,40);		

$graph->SetShadow();
$graph->title->Set("Linked scatter plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// 10% top and bottom grace
$graph->yscale->SetGrace(5,5);
$graph->xscale->SetGrace(1,1);

$sp1 = new ScatterPlot($datay,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor("red");
$sp1->SetColor("blue");

//$sp1->SetWeight(3);
$sp1->mark->SetWidth(4);
$sp1->SetLinkPoints();

$graph->Add($sp1);

$graph->Stroke();

?>


