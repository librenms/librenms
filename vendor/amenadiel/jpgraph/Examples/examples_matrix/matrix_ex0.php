<?php
require_once('jpgraph/jpgraph.php');
require_once('jpgraph/jpgraph_matrix.php');

// Some (random) matrix
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

// Setup a bsic matrix graph and title
$graph = new MatrixGraph(400,300);
$graph->title->Set('Basic matrix example');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);

// Create a ,atrix plot using all default values
$mp = new MatrixPlot($data);
$graph->Add($mp);

$graph->Stroke();

?>
