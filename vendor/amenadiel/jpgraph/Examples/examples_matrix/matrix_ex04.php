<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_matrix.php');
require_once ('jpgraph/jpgraph_iconplot.php');

$data = array(
	array(0,1,2,3,4,5,6,7,8,9,10),
	array(10,9,8,7,6,5,4,3,2,1,0),
	array(0,1,2,3,4,5,6,7,8,9,10),
	array(10,9,8,17,6,5,4,3,2,1,0),
	array(0,1,2,3,4,4,9,7,8,9,10),
	array(8,1,2,3,4,8,3,7,8,9,10),
	array(10,3,5,7,6,5,4,3,12,1,0),
	array(10,9,8,7,6,5,4,3,2,1,0),
);

// Do the meshinterpolation once for the data
doMeshInterpolate($data,4);
$r=count($data);$c=count($data[0]);

$width=400; $height=400;
$graph = new MatrixGraph($width,$height);
$graph->title->Set('Adding a background image');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);

// Add a stretched background image
$graph->SetBackgroundImage('ironrod.jpg',BGIMG_FILLFRAME);
$graph->SetBackgroundImageMix(50);

$mp = new MatrixPlot($data,1);
$mp->SetSize(0.6);
$mp->SetCenterPos(0.5,0.5);
$mp->SetLegendLayout(1);

$graph->Add($mp);
$graph->Stroke();

?>
