<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(12,5,19,22,17,6);

// Create the graph. 
$graph = new Graph(400,300,"auto");	
$graph->img->SetMargin(60,30,50,40);
$graph->SetScale("textlin");
$graph->SetShadow();

$graph->title->SetFont(FF_ARIAL,FS_BOLD,15);
$graph->title->Set("Cash flow ");
$graph->subtitle->Set("Use of static line, 3D and solid band");

// Turn off Y-grid (it's on by default)
$graph->ygrid->Show(false);

// Add 10% grace ("space") at top of Y-scale. 
$graph->yscale->SetGrace(10);
$graph->yscale->SetAutoMin(-20);

// Turn the tick mark out from the plot area
$graph->xaxis->SetTickSide(SIDE_DOWN);
$graph->yaxis->SetTickSide(SIDE_LEFT);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("orange");
$bplot->SetShadow("darkblue");

// Show the actual value for each bar on top/bottom
$bplot->value->Show(true);
$bplot->value->SetFormat("%02d kr");

// Position the X-axis at the bottom of the plotare
$graph->xaxis->SetPos("min");

// .. and add the plot to the graph
$graph->Add($bplot);

// Add upper and lower band and use no frames
$band[0]=new PlotBand(HORIZONTAL,BAND_3DPLANE,"min",0,"blue");
$band[0]->ShowFrame(false);
$band[0]->SetDensity(20);
$band[1]=new PlotBand(HORIZONTAL,BAND_SOLID,0,"max","steelblue");
$band[1]->ShowFrame(false);
$graph->Add($band);

$graph->Add(new PlotLine(HORIZONTAL,0,"black",2));

//$graph->title->Set("Test of bar gradient fill");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,11);
$graph->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,11);

$graph->Stroke();
?>
