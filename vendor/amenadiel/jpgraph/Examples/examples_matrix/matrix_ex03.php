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
doMeshInterpolate($data,3);
$r=count($data);$c=count($data[0]);

$width=400; $height=400;
$graph = new MatrixGraph($width,$height);
$graph->title->Set('Adding an icon to the background');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);

$mp = new MatrixPlot($data,1);
$mp->SetSize(0.6);

$icon = new IconPlot('icon.jpg',$width-1,$height-1,0.8,50);
$icon->SetAnchor('right','bottom');
$graph->Add($icon);

$graph->Add($mp);
$graph->Stroke();

?>
