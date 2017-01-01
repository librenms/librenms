<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');

// Create the graph. 
$graph = new CanvasGraph(350,200);	

$t1 = new Text("This is a text with more text");
$t1->SetPos(0.05,0.5);
$t1->SetOrientation("h");
$t1->SetFont(FF_FONT1,FS_NORMAL);
$t1->SetBox("white","black",'gray');
$t1->SetColor("black");
$graph->AddText($t1);

$graph->Stroke();

?>
