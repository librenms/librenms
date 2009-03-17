<?php
// $Id: fixscale_radarex1.php,v 1.1 2002/08/21 21:28:16 aditus Exp $
include ("../jpgraph.php");
include ("../jpgraph_radar.php");
	
$graph = new RadarGraph(300,300,'auto');
$graph->SetScale("lin",0,50);
$graph->yscale->ticks->Set(25,5);
$graph->SetColor("white");
$graph->SetShadow();

$graph->SetCenter(0.5,0.55);

$graph->axis->SetFont(FF_FONT1,FS_BOLD);
$graph->axis->SetWeight(2);

// Uncomment the following lines to also show grid lines.
//$graph->grid->SetLineStyle("longdashed");
//$graph->grid->SetColor("navy");
//$graph->grid->Show();
	
$graph->ShowMinorTickMarks();
		
$graph->title->Set("Quality result");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->SetTitles(array("One","Two","Three","Four","Five","Sex","Seven","Eight","Nine","Ten"));
		
$plot = new RadarPlot(array(12,35,20,30,33,15,37));
$plot->SetLegend("Goal");
$plot->SetColor("red","lightred");
$plot->SetFillColor('lightblue');
$plot->SetLineWeight(2);

$graph->Add($plot);
$graph->Stroke();

?>
