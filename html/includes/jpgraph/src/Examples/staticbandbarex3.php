<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(12,0,-19,-7,17,-6);

// Create the graph. 
$graph = new Graph(400,300,"auto");	
$graph->img->SetMargin(60,30,50,40);
$graph->SetScale("textlin");
$graph->SetShadow();

$graph->title->SetFont(FF_ARIAL,FS_BOLD,15);
$graph->title->Set("Cash flow ");
$graph->subtitle->Set("(Department X)");

// Show both X and Y grid
$graph->xgrid->Show(true,false);

// Add 10% grace ("space") at top and botton of Y-scale. 
$graph->yscale->SetGrace(10,10);

// Turn the tick mark out from the plot area
$graph->xaxis->SetTickSide(SIDE_DOWN);
$graph->yaxis->SetTickSide(SIDE_LEFT);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("orange");

// Show the actual value for each bar on top/bottom
$bplot->value->Show(true);
$bplot->value->SetFormat("%02d kr");

// Position the X-axis at the bottom of the plotare
$graph->xaxis->SetPos("min");

// .. and add the plot to the graph
$graph->Add($bplot);

// Add upper and lower band and use no frames
$uband=new PlotBand(HORIZONTAL,BAND_RDIAG,0,"max","green");
$uband->ShowFrame(false);
$lband=new PlotBand(HORIZONTAL,BAND_LDIAG,"min",0,"red");
$lband->ShowFrame(false);

$graph->AddBand($uband);
$graph->AddBand($lband);

//$graph->title->Set("Test of bar gradient fill");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,11);
$graph->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,11);

$graph->Stroke();
?>
