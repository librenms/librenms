<?php

include("../jpgraph.php");
include("../jpgraph_line.php");

// Some data
$ydata = array(5,10,15,20,15,10,8,7,4,10,5);

// Create the graph
$graph= new Graph(400,300,"auto");
$graph->SetScale("textlin");
$graph->SetShadow(true);
$graph->SetMarginColor("lightblue");

// Setup format for legend
$graph->legend->SetFillColor("antiquewhite");
$graph->legend->SetShadow(true);

// Setup title
$graph->title->Set("Filled Area Example");
$graph->title->SetFont(FF_FONT2,FS_BOLD);

// Setup semi-filled line plot
$lineplot = new LinePlot($ydata);
$lineplot->SetLegend("Semi-filled\nLineplot");

// Set line color
$lineplot->SetColor("black");

// Setup the two areas to be filled
$lineplot->AddArea(2,5,LP_AREA_FILLED,"red");
$lineplot->AddArea(6,8,LP_AREA_FILLED,"green");

// Display the marks on the lines
$lineplot->mark->SetType(MARK_DIAMOND);
$lineplot->mark->SetSize(8);
$lineplot->mark->Show();

// add plot to the graph
$graph->Add($lineplot);

// display graph
$graph->Stroke();
?>