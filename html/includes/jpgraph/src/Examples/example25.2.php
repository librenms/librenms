<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(12,8,19,3,10,5);

// Create the graph. These two calls are always required
$graph = new Graph(300,200,"auto");	
$graph->SetScale("textlin");

// Add a drop shadow
$graph->SetShadow();

// Adjust the margin a bit to make more room for titles
$graph->img->SetMargin(40,30,20,40);

// Create a bar pot
$bplot = new BarPlot($datay);
$graph->Add($bplot);

// Create and add a new text
$txt=new Text("This is a text\nwith many\nand even\nmore\nlines of text");
$txt->Pos(0.5,0.5,"center","center");
$txt->SetFont(FF_FONT2,FS_BOLD);
$txt->ParagraphAlign('cenetered');
$txt->SetBox('yellow','navy','gray');
$txt->SetColor("red");
$graph->AddText($txt);


// Setup the titles
$graph->title->Set("A simple bar graph");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

// Display the graph
$graph->Stroke();
?>
