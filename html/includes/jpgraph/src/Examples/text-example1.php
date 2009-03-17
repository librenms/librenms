<?php
include ("../jpgraph.php");
include ("../jpgraph_canvas.php");

// Create the graph. 
$graph = new CanvasGraph(350,200,"auto");	

$t1 = new Text("This is a text with more text");
$t1->Pos(0.05,0.5);
$t1->SetOrientation("h");
$t1->SetFont(FF_FONT1,FS_NORMAL);
$t1->SetBox("white","black",'gray');
$t1->SetColor("black");
$graph->AddText($t1);

$graph->Stroke();

?>