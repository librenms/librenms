<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(570,220);

$nstyle = array(
	NEEDLE_STYLE_SIMPLE, NEEDLE_STYLE_STRAIGHT, NEEDLE_STYLE_ENDARROW,
	NEEDLE_STYLE_SMALL_TRIANGLE,NEEDLE_STYLE_MEDIUM_TRIANGLE,
	NEEDLE_STYLE_LARGE_TRIANGLE
);

$captions = array(
	"NEEDLE_STYLE_SIMPLE","NEEDLE_STYLE_STRAIGHT","NEEDLE_STYLE_ENDARROW",
	"NEEDLE_STYLE_SMALL_TRIANGLE","NEEDLE_STYLE_MEDIUM_TRIANGLE",
	"NEEDLE_STYLE_LARGE_TRIANGLE"
);

$odo = array();

for( $i=0; $i < 6; ++$i ) {
	$odo[$i] = new Odometer();
	$odo[$i]->SetColor("lightyellow");
	$odo[$i]->needle->Set(80);
	$odo[$i]->needle->SetStyle($nstyle[$i]);
	$odo[$i]->caption->Set($captions[$i]);
	$odo[$i]->caption->SetFont(FF_FONT1);
	$odo[$i]->caption->SetMargin(3);
}

// Use the automatic layout engine to positon the plots on the graph
$row1 = new LayoutHor( array($odo[0],$odo[1],$odo[2]) );
$row2 = new LayoutHor( array($odo[3],$odo[4],$odo[5]) );
$col1 = new LayoutVert( array($row1,$row2) );

// Add the odometer to the graph
$graph->Add($col1);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();

?>