<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');

// Create the graph. 
$graph = new CanvasGraph(350,200);	

$t1 = new Text("a good\nas you can see right now per see\nThis is a text with\nseveral lines\n");
$t1->SetPos(0.05,100);
$t1->SetFont(FF_FONT1,FS_NORMAL);
$t1->SetBox("white","black",true);
$t1->ParagraphAlign("right");
$t1->SetColor("black");
$graph->AddText($t1);

$graph->Stroke();

?>
