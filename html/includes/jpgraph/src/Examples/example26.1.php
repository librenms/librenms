<?php
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

$data = array(40,60,21,33);

$graph = new PieGraph(300,200,"auto");
$graph->SetShadow();

$graph->title->Set("A simple Pie plot");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot($data);
$p1->SetLegends($gDateLocale->GetShortMonth());
$p1->SetCenter(0.4);
$p1->SetValueType(PIE_VALUE_ABS);
$p1->value->SetFormat('%d');
$graph->Add($p1);
$graph->Stroke();

?>


