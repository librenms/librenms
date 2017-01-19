<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

// Create a new odometer graph (width=250, height=200 pixels)
$graph = new OdoGraph(600,300);

$odo = array();
$astyles = array(
	NEEDLE_ARROW_SS,NEEDLE_ARROW_SM,NEEDLE_ARROW_SL,
	NEEDLE_ARROW_MS,NEEDLE_ARROW_MM, NEEDLE_ARROW_ML,
	NEEDLE_ARROW_LS, NEEDLE_ARROW_LM, NEEDLE_ARROW_LL
	);
$acaptions = array(
	'SS','SM','SL','MS','MM','ML','LS','LM','LL'
);

for($i = 0; $i < 9; ++$i ) {
	$odo[$i] = 	new Odometer();
	$odo[$i]->SetColor("lightyellow");
	$odo[$i]->needle->Set(75);
	$odo[$i]->needle->SetStyle(NEEDLE_STYLE_ENDARROW, $astyles[$i]);
	$odo[$i]->caption->SetFont(FF_FONT1);
	$odo[$i]->caption->Set($acaptions[$i]);
	$odo[$i]->SetMargin(15);
}

$row1 = new LayoutHor( array($odo[0],$odo[1],$odo[2]) );
$row2 = new LayoutHor( array($odo[3],$odo[4],$odo[5]) );
$row3 = new LayoutHor( array($odo[6],$odo[7],$odo[8]) );
$col1 = new LayoutVert( array($row1,$row2,$row3) );

// Add the odometer to the graph
$graph->Add($col1);

// ... and finally stroke and stream the image back to the browser
$graph->Stroke();
?>