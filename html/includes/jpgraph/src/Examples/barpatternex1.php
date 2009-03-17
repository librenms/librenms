<?php

include ("../jpgraph.php");
include ("../jpgraph_bar.php");
include ("../jpgraph_line.php");

$datay=array(2,3,5,8.5,11.5,6,3);

// Create the graph. 
$graph = new Graph(350,300);	

$graph->SetScale("textlin");

$graph->SetMarginColor('navy:1.9');
$graph->SetBox();

$graph->title->Set('Bar Pattern');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,20);

$graph->SetTitleBackground('lightblue:1.3',TITLEBKG_STYLE2,TITLEBKG_FRAME_BEVEL);
$graph->SetTitleBackgroundFillStyle(TITLEBKG_FILLSTYLE_HSTRIPED,'lightblue','blue');

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor('darkorange');
$bplot->SetWidth(0.6);

$bplot->SetPattern(PATTERN_CROSS1,'navy');

$graph->Add($bplot);

$graph->Stroke();
?>
