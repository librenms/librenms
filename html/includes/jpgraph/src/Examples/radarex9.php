<?php
include ("../jpgraph.php"); 
include ("../jpgraph_radar.php");

$titles=array("N", '', "NW", '', "W", '', "SW", '', 'S', '', "SE", '', "E", '', "NE", '');
$data=array(0, 0, 8, 10, 70, 90, 42, 0, 70, 60, 50, 40, 30, 40, 37.8, 72);

$graph = new RadarGraph (250,270,"auto"); 

$graph->title->Set("Accumulated PPM");
$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);

$graph->subtitle->Set("(according to direction)");
$graph->subtitle->SetFont(FF_VERDANA,FS_ITALIC,10);


$graph->SetTitles($titles);
$graph->SetCenter(0.5,0.55);
$graph->HideTickMarks(); 
$graph->SetColor('lightyellow');
$graph->axis->SetColor('darkgray@0.3'); 
$graph->grid->SetColor('darkgray@0.3');
$graph->grid->Show();

$graph->SetGridDepth(DEPTH_BACK);

$plot = new RadarPlot($data);
$plot->SetColor('red@0.2');
$plot->SetLineWeight(1);
$plot->SetFillColor('red@0.7');
$graph->Add($plot);
$graph->Stroke();
?>
